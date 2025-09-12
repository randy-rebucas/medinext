import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Clock,
    User,
    AlertCircle,
    CheckCircle,
    Stethoscope,
    Eye,
    Edit,
    Trash2,
    ArrowUp,
    ArrowDown,
    RefreshCw
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type QueueItem, type Patient, type Encounter } from '@/types';

interface QueueManagementProps {
    initialQueue: QueueItem[];
    onQueueUpdate?: (queue: QueueItem[]) => void;
    onPatientSelect?: (patient: QueueItem) => void;
    onEncounterComplete?: (encounterId: number) => void;
    userRole: 'receptionist' | 'doctor';
    showActions?: boolean;
}

export function QueueManagement({
    initialQueue,
    onQueueUpdate,
    onPatientSelect,
    onEncounterComplete,
    userRole,
    showActions = true
}: QueueManagementProps) {
    const [queue, setQueue] = useState<QueueItem[]>(initialQueue);
    const [filteredQueue, setFilteredQueue] = useState<QueueItem[]>(initialQueue);
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [visitTypeFilter, setVisitTypeFilter] = useState('all');
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [selectedPatient, setSelectedPatient] = useState<QueueItem | null>(null);
    const [isDetailsDialogOpen, setIsDetailsDialogOpen] = useState(false);

    // Filter queue based on search and filters
    useEffect(() => {
        let filtered = queue;

        // Search filter
        if (searchQuery) {
            filtered = filtered.filter(item =>
                item.patient_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                item.encounter_number.toLowerCase().includes(searchQuery.toLowerCase()) ||
                item.patient.patient_id.toLowerCase().includes(searchQuery.toLowerCase())
            );
        }

        // Status filter
        if (statusFilter !== 'all') {
            filtered = filtered.filter(item => item.status === statusFilter);
        }

        // Visit type filter
        if (visitTypeFilter !== 'all') {
            filtered = filtered.filter(item => item.visit_type === visitTypeFilter);
        }

        setFilteredQueue(filtered);
    }, [queue, searchQuery, statusFilter, visitTypeFilter]);

    // Refresh queue data
    const refreshQueue = async () => {
        setIsRefreshing(true);
        try {
            const response = await fetch('/api/v1/queue/active', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setQueue(data.data || []);
                onQueueUpdate?.(data.data || []);
            }
        } catch (error) {
            console.error('Error refreshing queue:', error);
        } finally {
            setIsRefreshing(false);
        }
    };

    // Move patient up in queue
    const movePatientUp = async (queueItemId: number) => {
        try {
            const response = await fetch(`/api/v1/queue/${queueItemId}/position`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ direction: 'up' }),
            });

            if (response.ok) {
                refreshQueue();
            }
        } catch (error) {
            console.error('Error moving patient up:', error);
        }
    };

    // Move patient down in queue
    const movePatientDown = async (queueItemId: number) => {
        try {
            const response = await fetch(`/api/v1/queue/${queueItemId}/position`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ direction: 'down' }),
            });

            if (response.ok) {
                refreshQueue();
            }
        } catch (error) {
            console.error('Error moving patient down:', error);
        }
    };

    // Remove patient from queue
    const removeFromQueue = async (queueItemId: number) => {
        try {
            const response = await fetch(`/api/v1/queue/${queueItemId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                refreshQueue();
            }
        } catch (error) {
            console.error('Error removing patient from queue:', error);
        }
    };

    // Handle patient selection
    const handlePatientSelect = (patient: QueueItem) => {
        setSelectedPatient(patient);
        setIsDetailsDialogOpen(true);
        onPatientSelect?.(patient);
    };

    // Complete encounter
    const handleCompleteEncounter = async (encounterId: number) => {
        try {
            const response = await fetch(`/api/v1/encounters/${encounterId}/complete`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                onEncounterComplete?.(encounterId);
                refreshQueue();
            }
        } catch (error) {
            console.error('Error completing encounter:', error);
        }
    };

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in-progress':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getVisitTypeColor = (type: string) => {
        switch (type.toLowerCase()) {
            case 'emergency':
                return 'bg-red-100 text-red-800';
            case 'follow-up':
                return 'bg-blue-100 text-blue-800';
            case 'opd':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPriorityColor = (position: number) => {
        if (position <= 3) return 'bg-red-100 text-red-800';
        if (position <= 6) return 'bg-yellow-100 text-yellow-800';
        return 'bg-green-100 text-green-800';
    };

    return (
        <div className="space-y-4">
            {/* Queue Header with Filters */}
            <Card>
                <CardHeader>
                    <div className="flex items-center justify-between">
                        <div>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="h-5 w-5" />
                                Patient Queue ({filteredQueue.length})
                            </CardTitle>
                            <CardDescription>
                                Manage patient queue and encounter flow
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={refreshQueue}
                            disabled={isRefreshing}
                        >
                            <RefreshCw className={`h-4 w-4 mr-2 ${isRefreshing ? 'animate-spin' : ''}`} />
                            Refresh
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="flex gap-4 mb-4">
                        <div className="flex-1">
                            <Input
                                placeholder="Search patients..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                            />
                        </div>
                        <Select value={statusFilter} onValueChange={setStatusFilter}>
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="in-progress">In Progress</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={visitTypeFilter} onValueChange={setVisitTypeFilter}>
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Visit Type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="opd">OPD</SelectItem>
                                <SelectItem value="emergency">Emergency</SelectItem>
                                <SelectItem value="follow-up">Follow-up</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            {/* Queue Items */}
            <div className="space-y-3">
                {filteredQueue.length > 0 ? (
                    filteredQueue.map((item) => (
                        <Card key={item.id} className="hover:shadow-md transition-shadow">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-4">
                                        <div className="text-center">
                                            <Badge className={`${getPriorityColor(item.queue_position)} text-lg px-3 py-1`}>
                                                #{item.queue_position}
                                            </Badge>
                                            <p className="text-xs text-muted-foreground mt-1">
                                                {item.estimated_wait_time}
                                            </p>
                                        </div>

                                        <div className="space-y-1">
                                            <div className="flex items-center gap-2">
                                                <h4 className="font-semibold text-lg">{item.patient_name}</h4>
                                                <Badge className={getVisitTypeColor(item.visit_type)}>
                                                    {item.visit_type.toUpperCase()}
                                                </Badge>
                                                <Badge className={getStatusColor(item.status)}>
                                                    {item.status}
                                                </Badge>
                                            </div>

                                            <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                <div>
                                                    <p><strong>Patient ID:</strong> {item.patient.patient_id}</p>
                                                    <p><strong>DOB:</strong> {item.patient_dob} ({item.patient_sex})</p>
                                                </div>
                                                <div>
                                                    <p><strong>Encounter:</strong> {item.encounter_number}</p>
                                                    <p><strong>Arrived:</strong> {new Date(item.created_at).toLocaleTimeString()}</p>
                                                </div>
                                            </div>

                                            <p className="text-sm"><strong>Reason:</strong> {item.reason_for_visit}</p>

                                            {item.patient.allergies && item.patient.allergies.length > 0 && (
                                                <Badge variant="destructive" className="text-xs">
                                                    <AlertCircle className="h-3 w-3 mr-1" />
                                                    Allergies: {item.patient.allergies.join(', ')}
                                                </Badge>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-2">
                                        {userRole === 'doctor' && (
                                            <Button
                                                size="sm"
                                                onClick={() => handlePatientSelect(item)}
                                            >
                                                <Stethoscope className="h-4 w-4 mr-2" />
                                                Start Encounter
                                            </Button>
                                        )}

                                        {userRole === 'receptionist' && showActions && (
                                            <div className="flex gap-1">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => movePatientUp(item.id)}
                                                    disabled={item.queue_position === 1}
                                                >
                                                    <ArrowUp className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => movePatientDown(item.id)}
                                                >
                                                    <ArrowDown className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => removeFromQueue(item.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        )}

                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() => handlePatientSelect(item)}
                                        >
                                            <Eye className="h-4 w-4 mr-2" />
                                            View Details
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))
                ) : (
                    <Card>
                        <CardContent className="text-center py-12">
                            <Clock className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                            <h3 className="text-xl font-semibold mb-2">No patients in queue</h3>
                            <p className="text-muted-foreground">
                                {searchQuery || statusFilter !== 'all' || visitTypeFilter !== 'all'
                                    ? 'No patients match your current filters.'
                                    : 'The queue is currently empty. Patients will appear here when they check in.'
                                }
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>

            {/* Patient Details Dialog */}
            <Dialog open={isDetailsDialogOpen} onOpenChange={setIsDetailsDialogOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Patient Details - {selectedPatient?.patient_name}</DialogTitle>
                        <DialogDescription>
                            Complete patient information and encounter details
                        </DialogDescription>
                    </DialogHeader>
                    {selectedPatient && (
                        <PatientDetailsView
                            patient={selectedPatient}
                            onCompleteEncounter={handleCompleteEncounter}
                            userRole={userRole}
                        />
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}

// Patient Details View Component
function PatientDetailsView({
    patient,
    onCompleteEncounter,
    userRole
}: {
    patient: QueueItem;
    onCompleteEncounter: (encounterId: number) => void;
    userRole: 'receptionist' | 'doctor';
}) {

    return (
        <div className="space-y-6">
            {/* Patient Overview */}
            <div className="grid grid-cols-3 gap-4">
                <div>
                    <Label className="text-sm font-medium">Patient Name</Label>
                    <p className="text-sm">{patient.patient_name}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Patient ID</Label>
                    <p className="text-sm">{patient.patient.patient_id}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">DOB / Sex</Label>
                    <p className="text-sm">{patient.patient_dob} ({patient.patient_sex})</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Phone</Label>
                    <p className="text-sm">{patient.patient.contact.phone || 'N/A'}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Email</Label>
                    <p className="text-sm">{patient.patient.contact.email || 'N/A'}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Queue Position</Label>
                    <p className="text-sm">#{patient.queue_position}</p>
                </div>
            </div>

            {/* Encounter Information */}
            <div className="space-y-2">
                <Label className="text-sm font-medium">Encounter Information</Label>
                <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p><strong>Encounter Number:</strong> {patient.encounter_number}</p>
                        <p><strong>Visit Type:</strong> {patient.visit_type}</p>
                        <p><strong>Status:</strong> {patient.status}</p>
                    </div>
                    <div>
                        <p><strong>Reason for Visit:</strong> {patient.reason_for_visit}</p>
                        <p><strong>Arrived:</strong> {new Date(patient.created_at).toLocaleString()}</p>
                        <p><strong>Estimated Wait:</strong> {patient.estimated_wait_time}</p>
                    </div>
                </div>
            </div>

            {/* Medical Information */}
            {patient.patient.allergies && patient.patient.allergies.length > 0 && (
                <div className="space-y-2">
                    <Label className="text-sm font-medium text-red-600">Known Allergies</Label>
                    <div className="flex flex-wrap gap-2">
                        {patient.patient.allergies.map((allergy, index) => (
                            <Badge key={index} variant="destructive">
                                {allergy}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            {patient.patient.medical_history && patient.patient.medical_history.length > 0 && (
                <div className="space-y-2">
                    <Label className="text-sm font-medium">Medical History</Label>
                    <div className="flex flex-wrap gap-2">
                        {patient.patient.medical_history.map((condition, index) => (
                            <Badge key={index} variant="outline">
                                {condition}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            {/* Actions */}
            <div className="flex justify-end gap-2 pt-4">
                {userRole === 'doctor' && (
                    <Button onClick={() => onCompleteEncounter(patient.encounter_id)}>
                        <CheckCircle className="h-4 w-4 mr-2" />
                        Complete Encounter
                    </Button>
                )}
                <Button variant="outline">
                    <Eye className="h-4 w-4 mr-2" />
                    View Full History
                </Button>
            </div>
        </div>
    );
}
