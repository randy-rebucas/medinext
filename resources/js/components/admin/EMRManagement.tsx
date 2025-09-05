import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Users,
  Search,
  FileText,
  Calendar,
  Pill,
  Activity,
  Download,
  Eye,
  Edit,
  Plus,
} from 'lucide-react';

interface Patient {
  id: number;
  first_name: string;
  last_name: string;
  dob: string;
  sex: string;
  contact: {
    phone?: string;
    email?: string;
  };
  allergies?: string[];
  last_visit?: string;
  status: string;
}

interface Encounter {
  id: number;
  patient_id: number;
  doctor_id: number;
  date: string;
  type: string;
  chief_complaint: string;
  diagnosis: string;
  treatment_plan: string;
  soap_notes: {
    subjective?: string;
    objective?: string;
    assessment?: string;
    plan?: string;
  };
  prescriptions: unknown[];
  lab_results: unknown[];
}


export default function EMRManagement() {
  const [patients, setPatients] = useState<Patient[]>([]);
  const [selectedPatient, setSelectedPatient] = useState<Patient | null>(null);
  const [encounters, setEncounters] = useState<Encounter[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [activeTab, setActiveTab] = useState('patients');

  useEffect(() => {
    fetchPatients();
  }, []);

  useEffect(() => {
    if (selectedPatient) {
      fetchPatientEncounters(selectedPatient.id);
    }
  }, [selectedPatient]);

  const fetchPatients = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/v1/patients');
      const data = await response.json();
      setPatients(data.data || []);
    } catch (error) {
      console.error('Error fetching patients:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchPatientEncounters = async (patientId: number) => {
    try {
      const response = await fetch(`/api/v1/patients/${patientId}/encounters`);
      const data = await response.json();
      setEncounters(data.data || []);
    } catch (error) {
      console.error('Error fetching encounters:', error);
    }
  };

  const filteredPatients = patients.filter(patient => {
    const matchesSearch =
      patient.first_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      patient.last_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      `${patient.first_name} ${patient.last_name}`.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus = filterStatus === 'all' || patient.status === filterStatus;

    return matchesSearch && matchesStatus;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
        return 'bg-green-100 text-green-800';
      case 'inactive':
        return 'bg-gray-100 text-gray-800';
      case 'deceased':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

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
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Patient Medical Records</h2>
          <p className="text-gray-600">Access and manage your patients' electronic medical records and clinical history</p>
        </div>

        <div className="flex gap-2">
          <Button variant="outline" size="sm">
            <Plus className="h-4 w-4 mr-2" />
            Add Patient
          </Button>
          <Button variant="outline" size="sm">
            <Download className="h-4 w-4 mr-2" />
            Export
          </Button>
        </div>
      </div>

      {/* Search and Filters */}
      <div className="flex flex-col sm:flex-row gap-4">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
          <Input
            placeholder="Search patients by name..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="pl-10"
          />
        </div>

        <Select value={filterStatus} onValueChange={setFilterStatus}>
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
            <SelectItem value="deceased">Deceased</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="patients">Patients List</TabsTrigger>
          <TabsTrigger value="patient-details" disabled={!selectedPatient}>
            Patient Details
          </TabsTrigger>
        </TabsList>

        <TabsContent value="patients" className="mt-6">
          {/* Patients Grid */}
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredPatients.map((patient) => (
                <Card
                  key={patient.id}
                  className={`cursor-pointer hover:shadow-md transition-shadow ${
                    selectedPatient?.id === patient.id ? 'ring-2 ring-blue-500' : ''
                  }`}
                  onClick={() => setSelectedPatient(patient)}
                >
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div>
                        <CardTitle className="text-lg">
                          {patient.first_name} {patient.last_name}
                        </CardTitle>
                        <CardDescription>
                          {calculateAge(patient.dob)} years old • {patient.sex}
                        </CardDescription>
                      </div>
                      <Badge className={getStatusColor(patient.status)}>
                        {patient.status}
                      </Badge>
                    </div>
                  </CardHeader>

                  <CardContent className="space-y-3">
                    <div className="space-y-2">
                      {patient.contact.phone && (
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <span className="font-medium">Phone:</span>
                          <span>{patient.contact.phone}</span>
                        </div>
                      )}

                      {patient.contact.email && (
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <span className="font-medium">Email:</span>
                          <span>{patient.contact.email}</span>
                        </div>
                      )}

                      {patient.last_visit && (
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <Calendar className="h-4 w-4" />
                          <span>Last visit: {new Date(patient.last_visit).toLocaleDateString()}</span>
                        </div>
                      )}
                    </div>

                    {patient.allergies && patient.allergies.length > 0 && (
                      <div className="space-y-1">
                        <p className="text-sm font-medium text-red-600">Allergies:</p>
                        <div className="flex flex-wrap gap-1">
                          {patient.allergies.map((allergy, index) => (
                            <Badge key={index} variant="destructive" className="text-xs">
                              {allergy}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}

                    <div className="flex gap-2 pt-2">
                      <Button
                        size="sm"
                        variant="outline"
                        className="flex-1"
                        onClick={(e) => {
                          e.stopPropagation();
                          setSelectedPatient(patient);
                          setActiveTab('patient-details');
                        }}
                      >
                        <Eye className="h-4 w-4 mr-2" />
                        View EMR
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={(e) => e.stopPropagation()}
                      >
                        <Edit className="h-4 w-4" />
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}

          {filteredPatients.length === 0 && !loading && (
            <Card>
              <CardContent className="flex flex-col items-center justify-center py-12">
                <Users className="h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No patients found</h3>
                <p className="text-gray-500 text-center">
                  {searchTerm ? 'No patients match your search criteria.' : 'You don\'t have any assigned patients yet.'}
                </p>
              </CardContent>
            </Card>
          )}
        </TabsContent>

        <TabsContent value="patient-details" className="mt-6">
          {selectedPatient && (
            <div className="space-y-6">
              {/* Patient Header */}
              <Card>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <div>
                      <CardTitle className="text-2xl">
                        {selectedPatient.first_name} {selectedPatient.last_name}
                      </CardTitle>
                      <CardDescription>
                        Patient ID: {selectedPatient.id} • {calculateAge(selectedPatient.dob)} years old • {selectedPatient.sex}
                      </CardDescription>
                    </div>
                    <Badge className={getStatusColor(selectedPatient.status)}>
                      {selectedPatient.status}
                    </Badge>
                  </div>
                </CardHeader>

                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-3">
                      <h4 className="font-medium text-gray-900">Contact Information</h4>
                      {selectedPatient.contact.phone && (
                        <p className="text-sm text-gray-600">
                          <span className="font-medium">Phone:</span> {selectedPatient.contact.phone}
                        </p>
                      )}
                      {selectedPatient.contact.email && (
                        <p className="text-sm text-gray-600">
                          <span className="font-medium">Email:</span> {selectedPatient.contact.email}
                        </p>
                      )}
                    </div>

                    <div className="space-y-3">
                      <h4 className="font-medium text-gray-900">Medical Information</h4>
                      <p className="text-sm text-gray-600">
                        <span className="font-medium">Date of Birth:</span> {new Date(selectedPatient.dob).toLocaleDateString()}
                      </p>
                      {selectedPatient.allergies && selectedPatient.allergies.length > 0 && (
                        <div>
                          <p className="text-sm font-medium text-red-600">Allergies:</p>
                          <div className="flex flex-wrap gap-1 mt-1">
                            {selectedPatient.allergies.map((allergy, index) => (
                              <Badge key={index} variant="destructive" className="text-xs">
                                {allergy}
                              </Badge>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Medical History */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <FileText className="h-5 w-5" />
                    Medical History
                  </CardTitle>
                  <CardDescription>
                    Recent encounters and medical records
                  </CardDescription>
                </CardHeader>

                <CardContent>
                  {encounters.length === 0 ? (
                    <div className="text-center py-8">
                      <FileText className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                      <p className="text-gray-500">No medical encounters found for this patient.</p>
                    </div>
                  ) : (
                    <div className="space-y-4">
                      {encounters.map((encounter) => (
                        <Card key={encounter.id} className="border-l-4 border-l-blue-500">
                          <CardContent className="p-4">
                            <div className="flex items-center justify-between mb-3">
                              <div className="flex items-center gap-2">
                                <Calendar className="h-4 w-4 text-gray-500" />
                                <span className="font-medium">
                                  {new Date(encounter.date).toLocaleDateString()}
                                </span>
                                <Badge variant="outline" className="capitalize">
                                  {encounter.type.replace('_', ' ')}
                                </Badge>
                              </div>
                              <Button size="sm" variant="outline">
                                <Eye className="h-4 w-4 mr-2" />
                                View Details
                              </Button>
                            </div>

                            <div className="space-y-2">
                              <div>
                                <p className="text-sm font-medium text-gray-900">Chief Complaint:</p>
                                <p className="text-sm text-gray-600">{encounter.chief_complaint}</p>
                              </div>

                              <div>
                                <p className="text-sm font-medium text-gray-900">Diagnosis:</p>
                                <p className="text-sm text-gray-600">{encounter.diagnosis}</p>
                              </div>

                              <div>
                                <p className="text-sm font-medium text-gray-900">Treatment Plan:</p>
                                <p className="text-sm text-gray-600">{encounter.treatment_plan}</p>
                              </div>
                            </div>

                            <div className="flex gap-2 mt-4">
                              {encounter.prescriptions.length > 0 && (
                                <Badge variant="secondary" className="flex items-center gap-1">
                                  <Pill className="h-3 w-3" />
                                  {encounter.prescriptions.length} Prescriptions
                                </Badge>
                              )}

                              {encounter.lab_results.length > 0 && (
                                <Badge variant="secondary" className="flex items-center gap-1">
                                  <Activity className="h-3 w-3" />
                                  {encounter.lab_results.length} Lab Results
                                </Badge>
                              )}
                            </div>
                          </CardContent>
                        </Card>
                      ))}
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
}
