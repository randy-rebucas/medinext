import React, { useState, useEffect } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Calendar, Clock, User, MapPin, Phone, Mail, ChevronLeft, ChevronRight } from 'lucide-react';

interface Appointment {
  id: number;
  patient_name: string;
  patient_phone?: string;
  patient_email?: string;
  start_at: string;
  end_at: string;
  status: string;
  appointment_type: string;
  reason: string;
  room_name?: string;
  priority: string;
  notes?: string;
}


export default function ScheduleView() {
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [viewMode, setViewMode] = useState<'day' | 'week' | 'month'>('day');
  const [filterStatus, setFilterStatus] = useState<string>('all');

  useEffect(() => {
    fetchAppointments();
  }, [selectedDate, viewMode]);

  const fetchAppointments = async () => {
    try {
      setLoading(true);
      const response = await fetch(`/api/v1/appointments?date_from=${formatDate(selectedDate)}&date_to=${formatDate(getEndDate())}`);
      const data = await response.json();
      setAppointments(data.data || []);
    } catch (error) {
      console.error('Error fetching appointments:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (date: Date) => {
    return date.toISOString().split('T')[0];
  };

  const getEndDate = () => {
    const endDate = new Date(selectedDate);
    switch (viewMode) {
      case 'day':
        return endDate;
      case 'week':
        endDate.setDate(endDate.getDate() + 6);
        return endDate;
      case 'month':
        endDate.setMonth(endDate.getMonth() + 1);
        return endDate;
      default:
        return endDate;
    }
  };

  const navigateDate = (direction: 'prev' | 'next') => {
    const newDate = new Date(selectedDate);
    switch (viewMode) {
      case 'day':
        newDate.setDate(newDate.getDate() + (direction === 'next' ? 1 : -1));
        break;
      case 'week':
        newDate.setDate(newDate.getDate() + (direction === 'next' ? 7 : -7));
        break;
      case 'month':
        newDate.setMonth(newDate.getMonth() + (direction === 'next' ? 1 : -1));
        break;
    }
    setSelectedDate(newDate);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'scheduled':
        return 'bg-blue-100 text-blue-800';
      case 'confirmed':
        return 'bg-green-100 text-green-800';
      case 'in_progress':
        return 'bg-yellow-100 text-yellow-800';
      case 'completed':
        return 'bg-gray-100 text-gray-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      case 'no_show':
        return 'bg-orange-100 text-orange-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent':
        return 'bg-red-100 text-red-800';
      case 'high':
        return 'bg-orange-100 text-orange-800';
      case 'normal':
        return 'bg-blue-100 text-blue-800';
      case 'low':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const filteredAppointments = appointments.filter(appointment => {
    if (filterStatus === 'all') return true;
    return appointment.status === filterStatus;
  });

  const groupedAppointments = filteredAppointments.reduce((groups, appointment) => {
    const date = new Date(appointment.start_at).toDateString();
    if (!groups[date]) {
      groups[date] = [];
    }
    groups[date].push(appointment);
    return groups;
  }, {} as Record<string, Appointment[]>);

  return (
    <div className="space-y-6">
      {/* Header Controls */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">My Clinical Schedule</h2>
          <p className="text-gray-600">View and manage your patient appointments and clinical availability</p>
        </div>

        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={() => navigateDate('prev')}>
            <ChevronLeft className="h-4 w-4" />
          </Button>
          <div className="text-center min-w-[200px]">
            <p className="font-medium">
              {selectedDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
              })}
            </p>
          </div>
          <Button variant="outline" size="sm" onClick={() => navigateDate('next')}>
            <ChevronRight className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* View Controls */}
      <div className="flex flex-col sm:flex-row gap-4">
        <div className="flex gap-2">
          <Button
            variant={viewMode === 'day' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setViewMode('day')}
          >
            Day
          </Button>
          <Button
            variant={viewMode === 'week' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setViewMode('week')}
          >
            Week
          </Button>
          <Button
            variant={viewMode === 'month' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setViewMode('month')}
          >
            Month
          </Button>
        </div>

        <Select value={filterStatus} onValueChange={setFilterStatus}>
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem value="scheduled">Scheduled</SelectItem>
            <SelectItem value="confirmed">Confirmed</SelectItem>
            <SelectItem value="in_progress">In Progress</SelectItem>
            <SelectItem value="completed">Completed</SelectItem>
            <SelectItem value="cancelled">Cancelled</SelectItem>
          </SelectContent>
        </Select>

        <Button variant="outline" size="sm">
          <Calendar className="h-4 w-4 mr-2" />
          Add Appointment
        </Button>
      </div>

      {/* Appointments List */}
      {loading ? (
        <div className="flex justify-center items-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
      ) : (
        <div className="space-y-6">
          {Object.keys(groupedAppointments).length === 0 ? (
            <Card>
              <CardContent className="flex flex-col items-center justify-center py-12">
                <Calendar className="h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No appointments found</h3>
                <p className="text-gray-500 text-center">
                  You don't have any appointments for the selected period.
                </p>
              </CardContent>
            </Card>
          ) : (
            Object.entries(groupedAppointments).map(([date, dayAppointments]) => (
              <div key={date}>
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  {new Date(date).toLocaleDateString('en-US', {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric'
                  })}
                </h3>

                <div className="grid gap-4">
                  {dayAppointments
                    .sort((a, b) => new Date(a.start_at).getTime() - new Date(b.start_at).getTime())
                    .map((appointment) => (
                    <Card key={appointment.id} className="hover:shadow-md transition-shadow">
                      <CardContent className="p-6">
                        <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                          <div className="flex-1">
                            <div className="flex items-center gap-3 mb-3">
                              <h4 className="text-lg font-semibold text-gray-900">
                                {appointment.patient_name}
                              </h4>
                              <Badge className={getStatusColor(appointment.status)}>
                                {appointment.status.replace('_', ' ')}
                              </Badge>
                              <Badge variant="outline" className={getPriorityColor(appointment.priority)}>
                                {appointment.priority}
                              </Badge>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                              <div className="space-y-2">
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <Clock className="h-4 w-4" />
                                  <span>
                                    {new Date(appointment.start_at).toLocaleTimeString('en-US', {
                                      hour: '2-digit',
                                      minute: '2-digit'
                                    })} - {new Date(appointment.end_at).toLocaleTimeString('en-US', {
                                      hour: '2-digit',
                                      minute: '2-digit'
                                    })}
                                  </span>
                                </div>

                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <User className="h-4 w-4" />
                                  <span className="capitalize">{appointment.appointment_type.replace('_', ' ')}</span>
                                </div>

                                {appointment.room_name && (
                                  <div className="flex items-center gap-2 text-sm text-gray-600">
                                    <MapPin className="h-4 w-4" />
                                    <span>{appointment.room_name}</span>
                                  </div>
                                )}
                              </div>

                              <div className="space-y-2">
                                {appointment.patient_phone && (
                                  <div className="flex items-center gap-2 text-sm text-gray-600">
                                    <Phone className="h-4 w-4" />
                                    <span>{appointment.patient_phone}</span>
                                  </div>
                                )}

                                {appointment.patient_email && (
                                  <div className="flex items-center gap-2 text-sm text-gray-600">
                                    <Mail className="h-4 w-4" />
                                    <span>{appointment.patient_email}</span>
                                  </div>
                                )}
                              </div>
                            </div>

                            {appointment.reason && (
                              <div className="mt-3">
                                <p className="text-sm text-gray-700">
                                  <span className="font-medium">Reason:</span> {appointment.reason}
                                </p>
                              </div>
                            )}

                            {appointment.notes && (
                              <div className="mt-3">
                                <p className="text-sm text-gray-700">
                                  <span className="font-medium">Notes:</span> {appointment.notes}
                                </p>
                              </div>
                            )}
                          </div>

                          <div className="flex flex-col sm:flex-row gap-2">
                            {appointment.status === 'scheduled' && (
                              <>
                                <Button size="sm" variant="outline">
                                  Check In
                                </Button>
                                <Button size="sm" variant="outline">
                                  Reschedule
                                </Button>
                              </>
                            )}

                            {appointment.status === 'in_progress' && (
                              <Button size="sm" variant="outline">
                                Complete
                              </Button>
                            )}

                            <Button size="sm" variant="outline">
                              View Details
                            </Button>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
}
