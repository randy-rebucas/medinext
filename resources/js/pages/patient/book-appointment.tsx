import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { patientBookAppointment, patientDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Calendar,
    Clock,
    Stethoscope,
    User,
    FileText,
    CheckCircle,
    Shield,
    AlertCircle
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patient Portal',
        href: patientDashboard(),
    },
    {
        title: 'Book Appointment',
        href: patientBookAppointment(),
    },
];

interface Doctor {
    id: number;
    name: string;
    specialty: string;
    availableSlots: string[];
    rating: number;
}

interface BookAppointmentProps {
    user?: {
        id: number;
        name: string;
        email: string;
        patient_id: string;
        sex: string;
    };
    permissions?: string[];
    doctors?: Doctor[];
    availableSlots?: Array<{ date: string; time: string; doctor_id: number; }>;
}

export default function BookAppointment({
    user,
    permissions = [],
    doctors = []
}: BookAppointmentProps) {
    const hasPermission = (permission: string) => {
        return permissions?.includes(permission) ?? true;
    };

    const [formData, setFormData] = useState({
        doctor_id: '',
        appointment_date: '',
        appointment_time: '',
        appointment_type: 'consultation',
        reason: '',
        symptoms: '',
        notes: ''
    });

    const [errors, setErrors] = useState<Partial<typeof formData>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isSuccess, setIsSuccess] = useState(false);
    const [selectedDoctor, setSelectedDoctor] = useState<Doctor | null>(null);

    const handleInputChange = (field: keyof typeof formData, value: string) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when user starts typing
        if (errors[field]) {
            setErrors(prev => ({ ...prev, [field]: undefined }));
        }
    };

    const handleDoctorSelect = (doctorId: string) => {
        const doctor = doctors.find(d => d.id.toString() === doctorId);
        setSelectedDoctor(doctor || null);
        handleInputChange('doctor_id', doctorId);
    };

    const validateForm = (): boolean => {
        const newErrors: Partial<typeof formData> = {};

        if (!formData.doctor_id) newErrors.doctor_id = 'Please select a doctor';
        if (!formData.appointment_date) newErrors.appointment_date = 'Please select a date';
        if (!formData.appointment_time) newErrors.appointment_time = 'Please select a time';
        if (!formData.reason.trim()) newErrors.reason = 'Please provide a reason for the visit';

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setIsSubmitting(true);

        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Here you would make the actual API call
            console.log('Booking appointment:', formData);

            setIsSuccess(true);
            // Reset form after successful submission
            setTimeout(() => {
                setFormData({
                    doctor_id: '',
                    appointment_date: '',
                    appointment_time: '',
                    appointment_type: 'consultation',
                    reason: '',
                    symptoms: '',
                    notes: ''
                });
                setSelectedDoctor(null);
                setIsSuccess(false);
            }, 3000);
        } catch (error) {
            console.error('Error booking appointment:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleCancel = () => {
        window.history.back();
    };

    // Default doctors data if not provided
    const defaultDoctors: Doctor[] = [
        {
            id: 1,
            name: 'Dr. Sarah Johnson',
            specialty: 'Cardiology',
            availableSlots: ['09:00 AM', '10:30 AM', '02:00 PM'],
            rating: 4.9
        },
        {
            id: 2,
            name: 'Dr. Michael Brown',
            specialty: 'Pediatrics',
            availableSlots: ['08:30 AM', '11:00 AM', '03:30 PM'],
            rating: 4.8
        },
        {
            id: 3,
            name: 'Dr. Emily Davis',
            specialty: 'Dermatology',
            availableSlots: ['09:30 AM', '01:00 PM', '04:00 PM'],
            rating: 4.7
        }
    ];

    const doctorsList = doctors.length > 0 ? doctors : defaultDoctors;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Book Appointment - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="space-y-6 p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">Book Appointment</h1>
                                <p className="mt-2 text-rose-100">
                                    Schedule your appointment with our healthcare providers
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Patient
                                </Badge>
                                {user && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <User className="h-3 w-3" />
                                        {user.sex}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Success Message */}
                    {isSuccess && (
                        <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
                            <CardContent className="flex items-center gap-3 p-4">
                                <CheckCircle className="h-5 w-5 text-green-600 dark:text-green-400" />
                                <div>
                                    <h3 className="font-semibold text-green-800 dark:text-green-200">Appointment Booked Successfully!</h3>
                                    <p className="text-sm text-green-600 dark:text-green-300">
                                        Your appointment has been scheduled and you will receive a confirmation email.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <form onSubmit={handleSubmit}>
                        <div className="grid gap-6 lg:grid-cols-3">
                            {/* Appointment Form */}
                            <Card className="lg:col-span-2 border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                        <Calendar className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                        Appointment Details
                                    </CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Fill in your appointment information
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="doctor_id" className="text-slate-700 dark:text-slate-300">
                                                Select Doctor *
                                            </Label>
                                            <Select value={formData.doctor_id} onValueChange={handleDoctorSelect}>
                                                <SelectTrigger className={`border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400 ${errors.doctor_id ? 'border-red-500 dark:border-red-400' : ''}`}>
                                                    <SelectValue placeholder="Choose a doctor" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {doctorsList.map((doctor) => (
                                                        <SelectItem key={doctor.id} value={doctor.id.toString()}>
                                                            {doctor.name} - {doctor.specialty}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.doctor_id && (
                                                <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                    <AlertCircle className="h-3 w-3" />
                                                    {errors.doctor_id}
                                                </p>
                                            )}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="appointment_type" className="text-slate-700 dark:text-slate-300">
                                                Appointment Type *
                                            </Label>
                                            <Select value={formData.appointment_type} onValueChange={(value) => handleInputChange('appointment_type', value)}>
                                                <SelectTrigger className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="consultation">Consultation</SelectItem>
                                                    <SelectItem value="follow-up">Follow-up</SelectItem>
                                                    <SelectItem value="checkup">Checkup</SelectItem>
                                                    <SelectItem value="emergency">Emergency</SelectItem>
                                                    <SelectItem value="routine">Routine Visit</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="appointment_date" className="text-slate-700 dark:text-slate-300">
                                                Preferred Date *
                                            </Label>
                                            <Input
                                                id="appointment_date"
                                                type="date"
                                                value={formData.appointment_date}
                                                onChange={(e) => handleInputChange('appointment_date', e.target.value)}
                                                min={new Date().toISOString().split('T')[0]}
                                                className={`border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400 ${errors.appointment_date ? 'border-red-500 dark:border-red-400' : ''}`}
                                            />
                                            {errors.appointment_date && (
                                                <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                    <AlertCircle className="h-3 w-3" />
                                                    {errors.appointment_date}
                                                </p>
                                            )}
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="appointment_time" className="text-slate-700 dark:text-slate-300">
                                                Preferred Time *
                                            </Label>
                                            <Select value={formData.appointment_time} onValueChange={(value) => handleInputChange('appointment_time', value)}>
                                                <SelectTrigger className={`border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400 ${errors.appointment_time ? 'border-red-500 dark:border-red-400' : ''}`}>
                                                    <SelectValue placeholder="Select time" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {selectedDoctor?.availableSlots.map((slot) => (
                                                        <SelectItem key={slot} value={slot}>
                                                            {slot}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.appointment_time && (
                                                <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                    <AlertCircle className="h-3 w-3" />
                                                    {errors.appointment_time}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="reason" className="text-slate-700 dark:text-slate-300">
                                            Reason for Visit *
                                        </Label>
                                        <Textarea
                                            id="reason"
                                            value={formData.reason}
                                            onChange={(e) => handleInputChange('reason', e.target.value)}
                                            placeholder="Please describe the reason for your visit"
                                            rows={3}
                                            className={`border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400 ${errors.reason ? 'border-red-500 dark:border-red-400' : ''}`}
                                        />
                                        {errors.reason && (
                                            <p className="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                                <AlertCircle className="h-3 w-3" />
                                                {errors.reason}
                                            </p>
                                        )}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="symptoms" className="text-slate-700 dark:text-slate-300">
                                            Current Symptoms
                                        </Label>
                                        <Textarea
                                            id="symptoms"
                                            value={formData.symptoms}
                                            onChange={(e) => handleInputChange('symptoms', e.target.value)}
                                            placeholder="Describe any current symptoms"
                                            rows={3}
                                            className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="notes" className="text-slate-700 dark:text-slate-300">
                                            Additional Notes
                                        </Label>
                                        <Textarea
                                            id="notes"
                                            value={formData.notes}
                                            onChange={(e) => handleInputChange('notes', e.target.value)}
                                            placeholder="Any additional information"
                                            rows={2}
                                            className="border-slate-200 dark:border-slate-700 focus:border-rose-500 dark:focus:border-rose-400"
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Available Doctors */}
                            <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                                <CardHeader>
                                    <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                        <Stethoscope className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                        Available Doctors
                                    </CardTitle>
                                    <CardDescription className="text-slate-600 dark:text-slate-300">
                                        Select your preferred doctor
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {doctorsList.map((doctor) => (
                                        <div key={doctor.id} className={`p-4 border rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer ${
                                            selectedDoctor?.id === doctor.id
                                                ? 'border-rose-300 dark:border-rose-600 bg-rose-50 dark:bg-rose-900/20'
                                                : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'
                                        }`} onClick={() => handleDoctorSelect(doctor.id.toString())}>
                                            <div className="flex items-center justify-between mb-2">
                                                <h3 className="font-medium text-slate-900 dark:text-white">{doctor.name}</h3>
                                                <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{doctor.rating} ⭐</Badge>
                                            </div>
                                            <p className="text-sm text-slate-600 dark:text-slate-400 mb-3">{doctor.specialty}</p>
                                            <div className="space-y-2">
                                                <p className="text-sm font-medium text-slate-700 dark:text-slate-300">Available Times:</p>
                                                <div className="flex flex-wrap gap-2">
                                                    {doctor.availableSlots.map((slot) => (
                                                        <Button
                                                            key={slot}
                                                            variant="outline"
                                                            size="sm"
                                                            className={`border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-300 dark:hover:border-rose-600 transition-all duration-200 ${
                                                                formData.appointment_time === slot ? 'bg-rose-100 dark:bg-rose-900/30 border-rose-300 dark:border-rose-600' : ''
                                                            }`}
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                handleInputChange('appointment_time', slot);
                                                            }}
                                                        >
                                                            {slot}
                                                        </Button>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Appointment Summary */}
                        <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center text-lg font-semibold text-slate-900 dark:text-white">
                                    <CheckCircle className="mr-2 h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    Appointment Summary
                                </CardTitle>
                                <CardDescription className="text-slate-600 dark:text-slate-300">
                                    Review your appointment details before confirming
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <div className="flex items-center">
                                            <User className="mr-2 h-4 w-4 text-slate-600 dark:text-slate-400" />
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Patient:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white">{user?.name || 'John Doe'}</span>
                                        </div>
                                        <div className="flex items-center">
                                            <Stethoscope className="mr-2 h-4 w-4 text-slate-600 dark:text-slate-400" />
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Doctor:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white">{selectedDoctor?.name || 'Not selected'}</span>
                                        </div>
                                        <div className="flex items-center">
                                            <Calendar className="mr-2 h-4 w-4 text-slate-600 dark:text-slate-400" />
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Date:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white">
                                                {formData.appointment_date ? new Date(formData.appointment_date).toLocaleDateString() : 'Not selected'}
                                            </span>
                                        </div>
                                        <div className="flex items-center">
                                            <Clock className="mr-2 h-4 w-4 text-slate-600 dark:text-slate-400" />
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Time:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white">{formData.appointment_time || 'Not selected'}</span>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex items-center">
                                            <FileText className="mr-2 h-4 w-4 text-slate-600 dark:text-slate-400" />
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Type:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white capitalize">{formData.appointment_type}</span>
                                        </div>
                                        <div className="flex items-center">
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Specialty:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white">{selectedDoctor?.specialty || 'Not selected'}</span>
                                        </div>
                                        <div className="flex items-center">
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Duration:</span>
                                            <span className="ml-2 text-sm text-slate-900 dark:text-white">30 minutes</span>
                                        </div>
                                        <div className="flex items-center">
                                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Status:</span>
                                            <Badge variant="default" className="ml-2 bg-rose-600 hover:bg-rose-700">Pending</Badge>
                                        </div>
                                    </div>
                                </div>
                                {hasPermission('appointments.create') && (
                                    <div className="flex justify-end space-x-2 mt-6">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={handleCancel}
                                            className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-200"
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={isSubmitting}
                                            className="hover:bg-rose-600 hover:border-rose-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {isSubmitting ? (
                                                <>
                                                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                                    Booking...
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle className="mr-2 h-4 w-4" />
                                                    Confirm Appointment
                                                </>
                                            )}
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
