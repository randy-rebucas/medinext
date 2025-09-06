import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    Search, 
    User, 
    UserPlus, 
    AlertCircle, 
    Clock,
    Phone,
    Mail,
    MapPin,
    Calendar,
    Eye,
    Edit,
    Plus
} from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type Patient } from '@/types';

interface PatientSearchProps {
    onPatientSelect?: (patient: Patient) => void;
    onNewPatient?: () => void;
    placeholder?: string;
    showNewPatientButton?: boolean;
    autoSearch?: boolean;
    searchDelay?: number;
}

export function PatientSearch({ 
    onPatientSelect, 
    onNewPatient,
    placeholder = "Search by name or patient ID...",
    showNewPatientButton = true,
    autoSearch = true,
    searchDelay = 500
}: PatientSearchProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<Patient[]>([]);
    const [isSearching, setIsSearching] = useState(false);
    const [hasSearched, setHasSearched] = useState(false);
    const [selectedPatient, setSelectedPatient] = useState<Patient | null>(null);
    const [isPatientDialogOpen, setIsPatientDialogOpen] = useState(false);
    const [isNewPatientDialogOpen, setIsNewPatientDialogOpen] = useState(false);

    // Debounced search effect
    useEffect(() => {
        if (!autoSearch || !searchQuery.trim()) {
            setSearchResults([]);
            setHasSearched(false);
            return;
        }

        const timeoutId = setTimeout(() => {
            performSearch(searchQuery);
        }, searchDelay);

        return () => clearTimeout(timeoutId);
    }, [searchQuery, autoSearch, searchDelay]);

    // Perform patient search
    const performSearch = async (query: string) => {
        if (!query.trim()) return;

        setIsSearching(true);
        setHasSearched(true);

        try {
            const response = await fetch(`/api/v1/patients/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setSearchResults(data.data || []);
            } else {
                setSearchResults([]);
            }
        } catch (error) {
            console.error('Search error:', error);
            setSearchResults([]);
        } finally {
            setIsSearching(false);
        }
    };

    // Handle manual search
    const handleSearch = () => {
        if (searchQuery.trim()) {
            performSearch(searchQuery);
        }
    };

    // Handle patient selection
    const handlePatientSelect = (patient: Patient) => {
        setSelectedPatient(patient);
        setIsPatientDialogOpen(true);
        onPatientSelect?.(patient);
    };

    // Handle new patient creation
    const handleNewPatient = () => {
        setIsNewPatientDialogOpen(true);
        onNewPatient?.();
    };

    // Calculate age from date of birth
    const calculateAge = (dob: string) => {
        const today = new Date();
        const birthDate = new Date(dob);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    };

    // Format date
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString();
    };

    return (
        <div className="space-y-4">
            {/* Search Input */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Search className="h-5 w-5" />
                        Patient Search
                    </CardTitle>
                    <CardDescription>
                        Search for existing patients by name or patient ID
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="flex gap-2">
                        <Input
                            placeholder={placeholder}
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                        />
                        {!autoSearch && (
                            <Button onClick={handleSearch} disabled={isSearching || !searchQuery.trim()}>
                                <Search className="h-4 w-4 mr-2" />
                                {isSearching ? 'Searching...' : 'Search'}
                            </Button>
                        )}
                        {showNewPatientButton && (
                            <Dialog open={isNewPatientDialogOpen} onOpenChange={setIsNewPatientDialogOpen}>
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        <UserPlus className="h-4 w-4 mr-2" />
                                        New Patient
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="max-w-2xl">
                                    <DialogHeader>
                                        <DialogTitle>Register New Patient</DialogTitle>
                                        <DialogDescription>
                                            Enter the patient's information to create a new record
                                        </DialogDescription>
                                    </DialogHeader>
                                    <NewPatientForm onSuccess={() => setIsNewPatientDialogOpen(false)} />
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Search Results */}
            {hasSearched && (
                <Card>
                    <CardHeader>
                        <CardTitle>Search Results</CardTitle>
                        <CardDescription>
                            {isSearching ? 'Searching...' : `${searchResults.length} patient(s) found`}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {isSearching ? (
                            <div className="text-center py-8">
                                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                                <p className="text-muted-foreground mt-2">Searching patients...</p>
                            </div>
                        ) : searchResults.length > 0 ? (
                            <div className="space-y-3">
                                {searchResults.map((patient) => (
                                    <Card key={patient.id} className="p-4 hover:shadow-md transition-shadow">
                                        <div className="flex items-center justify-between">
                                            <div className="space-y-2">
                                                <div className="flex items-center gap-3">
                                                    <User className="h-5 w-5 text-muted-foreground" />
                                                    <h4 className="font-semibold text-lg">{patient.name}</h4>
                                                    <Badge variant="outline">
                                                        ID: {patient.patient_id}
                                                    </Badge>
                                                </div>
                                                
                                                <div className="grid grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                    <div className="space-y-1">
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4" />
                                                            <span>DOB: {formatDate(patient.dob)} (Age: {calculateAge(patient.dob)})</span>
                                                        </div>
                                                        <div className="flex items-center gap-2">
                                                            <User className="h-4 w-4" />
                                                            <span>Sex: {patient.sex}</span>
                                                        </div>
                                                    </div>
                                                    <div className="space-y-1">
                                                        {patient.contact.phone && (
                                                            <div className="flex items-center gap-2">
                                                                <Phone className="h-4 w-4" />
                                                                <span>{patient.contact.phone}</span>
                                                            </div>
                                                        )}
                                                        {patient.contact.email && (
                                                            <div className="flex items-center gap-2">
                                                                <Mail className="h-4 w-4" />
                                                                <span>{patient.contact.email}</span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>

                                                {patient.address && (
                                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                        <MapPin className="h-4 w-4" />
                                                        <span>{patient.address}</span>
                                                    </div>
                                                )}

                                                {patient.allergies && patient.allergies.length > 0 && (
                                                    <div className="flex items-center gap-2">
                                                        <AlertCircle className="h-4 w-4 text-red-500" />
                                                        <span className="text-sm text-red-600">
                                                            Allergies: {patient.allergies.join(', ')}
                                                        </span>
                                                    </div>
                                                )}

                                                {patient.last_visit && (
                                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                        <Clock className="h-4 w-4" />
                                                        <span>Last visit: {formatDate(patient.last_visit)}</span>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="flex gap-2">
                                                <Button 
                                                    size="sm" 
                                                    onClick={() => handlePatientSelect(patient)}
                                                >
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    Select
                                                </Button>
                                                <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    onClick={() => handlePatientSelect(patient)}
                                                >
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    View Details
                                                </Button>
                                            </div>
                                        </div>
                                    </Card>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8">
                                <AlertCircle className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">No patients found</h3>
                                <p className="text-muted-foreground mb-4">
                                    No patients match your search criteria. Try a different search term or register a new patient.
                                </p>
                                {showNewPatientButton && (
                                    <Button onClick={handleNewPatient}>
                                        <UserPlus className="h-4 w-4 mr-2" />
                                        Register New Patient
                                    </Button>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            )}

            {/* Patient Details Dialog */}
            <Dialog open={isPatientDialogOpen} onOpenChange={setIsPatientDialogOpen}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Patient Details</DialogTitle>
                        <DialogDescription>
                            Complete patient information and medical history
                        </DialogDescription>
                    </DialogHeader>
                    {selectedPatient && (
                        <PatientDetailsView 
                            patient={selectedPatient}
                            onClose={() => setIsPatientDialogOpen(false)}
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
    onClose 
}: { 
    patient: Patient;
    onClose: () => void;
}) {
    const calculateAge = (dob: string) => {
        const today = new Date();
        const birthDate = new Date(dob);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    };

    return (
        <div className="space-y-6">
            {/* Basic Information */}
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label className="text-sm font-medium">Full Name</Label>
                    <p className="text-sm">{patient.name}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Patient ID</Label>
                    <p className="text-sm">{patient.patient_id}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Date of Birth</Label>
                    <p className="text-sm">{new Date(patient.dob).toLocaleDateString()}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Age</Label>
                    <p className="text-sm">{calculateAge(patient.dob)} years</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Sex</Label>
                    <p className="text-sm">{patient.sex}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Phone</Label>
                    <p className="text-sm">{patient.contact.phone || 'Not provided'}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Email</Label>
                    <p className="text-sm">{patient.contact.email || 'Not provided'}</p>
                </div>
                <div>
                    <Label className="text-sm font-medium">Emergency Contact</Label>
                    <p className="text-sm">{patient.emergency_contact || 'Not provided'}</p>
                </div>
            </div>

            {/* Address */}
            {patient.address && (
                <div>
                    <Label className="text-sm font-medium">Address</Label>
                    <p className="text-sm">{patient.address}</p>
                </div>
            )}

            {/* Allergies */}
            {patient.allergies && patient.allergies.length > 0 && (
                <div>
                    <Label className="text-sm font-medium text-red-600">Known Allergies</Label>
                    <div className="flex flex-wrap gap-2 mt-1">
                        {patient.allergies.map((allergy, index) => (
                            <Badge key={index} variant="destructive">
                                {allergy}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            {/* Medical History */}
            {patient.medical_history && patient.medical_history.length > 0 && (
                <div>
                    <Label className="text-sm font-medium">Medical History</Label>
                    <div className="flex flex-wrap gap-2 mt-1">
                        {patient.medical_history.map((condition, index) => (
                            <Badge key={index} variant="outline">
                                {condition}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            {/* Last Visit */}
            {patient.last_visit && (
                <div>
                    <Label className="text-sm font-medium">Last Visit</Label>
                    <p className="text-sm">{new Date(patient.last_visit).toLocaleDateString()}</p>
                </div>
            )}

            {/* Actions */}
            <div className="flex justify-end gap-2 pt-4">
                <Button variant="outline" onClick={onClose}>
                    Close
                </Button>
                <Button>
                    <Plus className="h-4 w-4 mr-2" />
                    New Encounter
                </Button>
            </div>
        </div>
    );
}

// New Patient Registration Form Component
function NewPatientForm({ onSuccess }: { onSuccess: () => void }) {
    const [formData, setFormData] = useState({
        name: '',
        dob: '',
        sex: '',
        phone: '',
        email: '',
        address: '',
        emergency_contact: '',
        allergies: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const response = await fetch('/api/v1/patients', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...formData,
                    allergies: formData.allergies ? formData.allergies.split(',').map(a => a.trim()) : []
                }),
            });

            if (response.ok) {
                onSuccess();
                // Reset form
                setFormData({
                    name: '',
                    dob: '',
                    sex: '',
                    phone: '',
                    email: '',
                    address: '',
                    emergency_contact: '',
                    allergies: ''
                });
            }
        } catch (error) {
            console.error('Error creating patient:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="name">Full Name *</Label>
                    <Input
                        id="name"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="dob">Date of Birth *</Label>
                    <Input
                        id="dob"
                        type="date"
                        value={formData.dob}
                        onChange={(e) => setFormData({ ...formData, dob: e.target.value })}
                        required
                    />
                </div>
                <div>
                    <Label htmlFor="sex">Sex *</Label>
                    <Select value={formData.sex} onValueChange={(value) => setFormData({ ...formData, sex: value })}>
                        <SelectTrigger>
                            <SelectValue placeholder="Select sex" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="male">Male</SelectItem>
                            <SelectItem value="female">Female</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label htmlFor="phone">Phone Number</Label>
                    <Input
                        id="phone"
                        value={formData.phone}
                        onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    />
                </div>
                <div>
                    <Label htmlFor="emergency_contact">Emergency Contact</Label>
                    <Input
                        id="emergency_contact"
                        value={formData.emergency_contact}
                        onChange={(e) => setFormData({ ...formData, emergency_contact: e.target.value })}
                    />
                </div>
            </div>
            <div>
                <Label htmlFor="address">Address</Label>
                <Textarea
                    id="address"
                    value={formData.address}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                />
            </div>
            <div>
                <Label htmlFor="allergies">Known Allergies</Label>
                <Textarea
                    id="allergies"
                    value={formData.allergies}
                    onChange={(e) => setFormData({ ...formData, allergies: e.target.value })}
                    placeholder="List any known allergies (separated by commas)..."
                />
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={onSuccess}>
                    Cancel
                </Button>
                <Button type="submit" disabled={isSubmitting}>
                    {isSubmitting ? 'Creating...' : 'Create Patient'}
                </Button>
            </div>
        </form>
    );
}
