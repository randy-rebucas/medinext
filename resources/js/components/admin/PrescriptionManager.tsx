import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import {
  FileText,
  Search,
  Plus,
  Eye,
  Edit,
  Download,
  CheckCircle,
  Pill,
  User,
  Calendar,
} from 'lucide-react';

interface Prescription {
  id: number;
  prescription_number: string;
  patient_name: string;
  doctor_name: string;
  issued_at: string;
  status: string;
  prescription_type: string;
  items_count: number;
  refills_remaining: number;
  expiry_date: string;
  total_cost: number;
  verification_status: string;
  diagnosis: string;
  instructions: string;
}

interface PrescriptionItem {
  id: number;
  medication_name: string;
  dosage: string;
  frequency: string;
  duration: string;
  quantity: number;
  instructions: string;
}

// eslint-disable-next-line @typescript-eslint/no-empty-interface
interface PrescriptionManagerProps {
  // Add props here if needed
}

export default function PrescriptionManager() {
  const [prescriptions, setPrescriptions] = useState<Prescription[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [filterType, setFilterType] = useState<string>('all');
  const [activeTab, setActiveTab] = useState('all');
  const [selectedPrescription, setSelectedPrescription] = useState<Prescription | null>(null);
  const [showCreateDialog, setShowCreateDialog] = useState(false);

  useEffect(() => {
    fetchPrescriptions();
  }, []);

  const fetchPrescriptions = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/v1/prescriptions');
      const data = await response.json();
      setPrescriptions(data.data || []);
    } catch (error) {
      console.error('Error fetching prescriptions:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredPrescriptions = prescriptions.filter(prescription => {
    const matchesSearch =
      prescription.prescription_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
      prescription.patient_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      prescription.diagnosis.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus = filterStatus === 'all' || prescription.status === filterStatus;
    const matchesType = filterType === 'all' || prescription.prescription_type === filterType;

    return matchesSearch && matchesStatus && matchesType;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
        return 'bg-green-100 text-green-800';
      case 'draft':
        return 'bg-gray-100 text-gray-800';
      case 'dispensed':
        return 'bg-blue-100 text-blue-800';
      case 'expired':
        return 'bg-red-100 text-red-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      case 'pending_verification':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getVerificationColor = (status: string) => {
    switch (status) {
      case 'verified':
        return 'bg-green-100 text-green-800';
      case 'rejected':
        return 'bg-red-100 text-red-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getTypeColor = (type: string) => {
    switch (type) {
      case 'new':
        return 'bg-blue-100 text-blue-800';
      case 'refill':
        return 'bg-green-100 text-green-800';
      case 'emergency':
        return 'bg-red-100 text-red-800';
      case 'controlled':
        return 'bg-purple-100 text-purple-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getTabPrescriptions = (tab: string) => {
    switch (tab) {
      case 'active':
        return filteredPrescriptions.filter(p => p.status === 'active');
      case 'pending':
        return filteredPrescriptions.filter(p => p.status === 'pending_verification');
      case 'expired':
        return filteredPrescriptions.filter(p => p.status === 'expired');
      case 'needs-refill':
        return filteredPrescriptions.filter(p => p.refills_remaining > 0 && p.status === 'active');
      default:
        return filteredPrescriptions;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Prescription Management</h2>
          <p className="text-gray-600">Create, manage, and track patient prescriptions and medication orders</p>
        </div>

        <div className="flex gap-2">
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                New Prescription
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle>Create New Prescription</DialogTitle>
                <DialogDescription>
                  Create a new prescription for a patient
                </DialogDescription>
              </DialogHeader>
              <CreatePrescriptionForm onClose={() => setShowCreateDialog(false)} />
            </DialogContent>
          </Dialog>

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
            placeholder="Search prescriptions by number, patient, or diagnosis..."
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
            <SelectItem value="draft">Draft</SelectItem>
            <SelectItem value="dispensed">Dispensed</SelectItem>
            <SelectItem value="expired">Expired</SelectItem>
            <SelectItem value="pending_verification">Pending Verification</SelectItem>
          </SelectContent>
        </Select>

        <Select value={filterType} onValueChange={setFilterType}>
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Filter by type" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Types</SelectItem>
            <SelectItem value="new">New</SelectItem>
            <SelectItem value="refill">Refill</SelectItem>
            <SelectItem value="emergency">Emergency</SelectItem>
            <SelectItem value="controlled">Controlled</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {/* Tabs */}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid w-full grid-cols-5">
          <TabsTrigger value="all">All Prescriptions</TabsTrigger>
          <TabsTrigger value="active">Active</TabsTrigger>
          <TabsTrigger value="pending">Pending</TabsTrigger>
          <TabsTrigger value="expired">Expired</TabsTrigger>
          <TabsTrigger value="needs-refill">Needs Refill</TabsTrigger>
        </TabsList>

        <TabsContent value={activeTab} className="mt-6">
          {/* Prescriptions List */}
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
          ) : (
            <div className="space-y-4">
              {getTabPrescriptions(activeTab).length === 0 ? (
                <Card>
                  <CardContent className="flex flex-col items-center justify-center py-12">
                    <FileText className="h-12 w-12 text-gray-400 mb-4" />
                    <h3 className="text-lg font-medium text-gray-900 mb-2">No prescriptions found</h3>
                    <p className="text-gray-500 text-center">
                      {searchTerm ? 'No prescriptions match your search criteria.' : 'No prescriptions found for the selected filter.'}
                    </p>
                  </CardContent>
                </Card>
              ) : (
                getTabPrescriptions(activeTab).map((prescription) => (
                  <Card key={prescription.id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-6">
                      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div className="flex-1">
                          <div className="flex items-center gap-3 mb-3">
                            <h4 className="text-lg font-semibold text-gray-900">
                              {prescription.prescription_number}
                            </h4>
                            <Badge className={getStatusColor(prescription.status)}>
                              {prescription.status.replace('_', ' ')}
                            </Badge>
                            <Badge variant="outline" className={getTypeColor(prescription.prescription_type)}>
                              {prescription.prescription_type}
                            </Badge>
                            <Badge variant="outline" className={getVerificationColor(prescription.verification_status)}>
                              {prescription.verification_status}
                            </Badge>
                          </div>

                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <User className="h-4 w-4" />
                                <span className="font-medium">Patient:</span>
                                <span>{prescription.patient_name}</span>
                              </div>

                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Calendar className="h-4 w-4" />
                                <span className="font-medium">Issued:</span>
                                <span>{new Date(prescription.issued_at).toLocaleDateString()}</span>
                              </div>

                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Pill className="h-4 w-4" />
                                <span className="font-medium">Items:</span>
                                <span>{prescription.items_count} medications</span>
                              </div>
                            </div>

                            <div className="space-y-2">
                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <span className="font-medium">Refills:</span>
                                <span>{prescription.refills_remaining} remaining</span>
                              </div>

                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <span className="font-medium">Expires:</span>
                                <span>{new Date(prescription.expiry_date).toLocaleDateString()}</span>
                              </div>

                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <span className="font-medium">Cost:</span>
                                <span>${prescription.total_cost?.toFixed(2) || '0.00'}</span>
                              </div>
                            </div>
                          </div>

                          {prescription.diagnosis && (
                            <div className="mt-3">
                              <p className="text-sm text-gray-700">
                                <span className="font-medium">Diagnosis:</span> {prescription.diagnosis}
                              </p>
                            </div>
                          )}

                          {prescription.instructions && (
                            <div className="mt-3">
                              <p className="text-sm text-gray-700">
                                <span className="font-medium">Instructions:</span> {prescription.instructions}
                              </p>
                            </div>
                          )}
                        </div>

                        <div className="flex flex-col sm:flex-row gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => setSelectedPrescription(prescription)}
                          >
                            <Eye className="h-4 w-4 mr-2" />
                            View Details
                          </Button>

                          {prescription.status === 'draft' && (
                            <Button size="sm" variant="outline">
                              <Edit className="h-4 w-4 mr-2" />
                              Edit
                            </Button>
                          )}

                          {prescription.status === 'active' && (
                            <Button size="sm" variant="outline">
                              <Download className="h-4 w-4 mr-2" />
                              Download PDF
                            </Button>
                          )}

                          {prescription.verification_status === 'pending' && (
                            <Button size="sm" variant="outline">
                              <CheckCircle className="h-4 w-4 mr-2" />
                              Verify
                            </Button>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))
              )}
            </div>
          )}
        </TabsContent>
      </Tabs>

      {/* Prescription Details Dialog */}
      {selectedPrescription && (
        <Dialog open={!!selectedPrescription} onOpenChange={() => setSelectedPrescription(null)}>
          <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Prescription Details</DialogTitle>
              <DialogDescription>
                {selectedPrescription.prescription_number} - {selectedPrescription.patient_name}
              </DialogDescription>
            </DialogHeader>
            <PrescriptionDetails prescription={selectedPrescription} />
          </DialogContent>
        </Dialog>
      )}
    </div>
  );
}

// Create Prescription Form Component
function CreatePrescriptionForm({ onClose }: { onClose: () => void }) {
  const [formData, setFormData] = useState({
    patient_id: '',
    prescription_type: 'new',
    diagnosis: '',
    instructions: '',
    items: [] as PrescriptionItem[]
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const response = await fetch('/api/v1/prescriptions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        onClose();
        // Refresh prescriptions list
      }
    } catch (error) {
      console.error('Error creating prescription:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Patient
          </label>
          <Select value={formData.patient_id} onValueChange={(value) => setFormData({...formData, patient_id: value})}>
            <SelectTrigger>
              <SelectValue placeholder="Select patient" />
            </SelectTrigger>
            <SelectContent>
              {/* Patient options would be loaded here */}
            </SelectContent>
          </Select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Prescription Type
          </label>
          <Select value={formData.prescription_type} onValueChange={(value) => setFormData({...formData, prescription_type: value})}>
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="new">New Prescription</SelectItem>
              <SelectItem value="refill">Refill</SelectItem>
              <SelectItem value="emergency">Emergency</SelectItem>
              <SelectItem value="controlled">Controlled Substance</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">
          Diagnosis
        </label>
        <Input
          value={formData.diagnosis}
          onChange={(e) => setFormData({...formData, diagnosis: e.target.value})}
          placeholder="Enter diagnosis"
        />
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-2">
          Instructions
        </label>
        <Input
          value={formData.instructions}
          onChange={(e) => setFormData({...formData, instructions: e.target.value})}
          placeholder="Enter general instructions"
        />
      </div>

      <div className="flex justify-end gap-2">
        <Button type="button" variant="outline" onClick={onClose}>
          Cancel
        </Button>
        <Button type="submit">
          Create Prescription
        </Button>
      </div>
    </form>
  );
}

// Prescription Details Component
function PrescriptionDetails({ prescription }: { prescription: Prescription }) {
  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Prescription Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <span className="font-medium">Number:</span> {prescription.prescription_number}
            </div>
            <div>
              <span className="font-medium">Status:</span>
              <Badge className={getStatusColor(prescription.status)}>
                {prescription.status.replace('_', ' ')}
              </Badge>
            </div>
            <div>
              <span className="font-medium">Type:</span>
              <Badge variant="outline" className={getTypeColor(prescription.prescription_type)}>
                {prescription.prescription_type}
              </Badge>
            </div>
            <div>
              <span className="font-medium">Issued:</span> {new Date(prescription.issued_at).toLocaleDateString()}
            </div>
            <div>
              <span className="font-medium">Expires:</span> {new Date(prescription.expiry_date).toLocaleDateString()}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Patient Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <span className="font-medium">Patient:</span> {prescription.patient_name}
            </div>
            <div>
              <span className="font-medium">Doctor:</span> {prescription.doctor_name}
            </div>
            <div>
              <span className="font-medium">Refills:</span> {prescription.refills_remaining} remaining
            </div>
            <div>
              <span className="font-medium">Total Cost:</span> ${prescription.total_cost?.toFixed(2) || '0.00'}
            </div>
          </CardContent>
        </Card>
      </div>

      {prescription.diagnosis && (
        <Card>
          <CardHeader>
            <CardTitle>Diagnosis</CardTitle>
          </CardHeader>
          <CardContent>
            <p>{prescription.diagnosis}</p>
          </CardContent>
        </Card>
      )}

      {prescription.instructions && (
        <Card>
          <CardHeader>
            <CardTitle>Instructions</CardTitle>
          </CardHeader>
          <CardContent>
            <p>{prescription.instructions}</p>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

// Helper functions (these would be defined at the component level)
function getStatusColor(status: string) {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800';
    case 'draft':
      return 'bg-gray-100 text-gray-800';
    case 'dispensed':
      return 'bg-blue-100 text-blue-800';
    case 'expired':
      return 'bg-red-100 text-red-800';
    case 'cancelled':
      return 'bg-red-100 text-red-800';
    case 'pending_verification':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

function getTypeColor(type: string) {
  switch (type) {
    case 'new':
      return 'bg-blue-100 text-blue-800';
    case 'refill':
      return 'bg-green-100 text-green-800';
    case 'emergency':
      return 'bg-red-100 text-red-800';
    case 'controlled':
      return 'bg-purple-100 text-purple-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}
