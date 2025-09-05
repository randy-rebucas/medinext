import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
  Pill,
  Search,
  Eye,
  Download,
  Calendar,
    Building,
  Package,
  AlertTriangle,
  CheckCircle,
  Clock,
  Phone,
  Mail
} from 'lucide-react';

interface MedrepVisit {
  id: number;
  medrep_id: number;
  medrep_name: string;
  medrep_company: string;
  medrep_contact: {
    phone?: string;
    email?: string;
  };
  start_at: string;
  end_at: string;
  purpose: string;
  notes: string;
  status: string;
  samples_provided: MedSample[];
}

interface MedSample {
  id: number;
  medication_name: string;
  generic_name: string;
  manufacturer: string;
  dosage_form: string;
  strength: string;
  quantity: number;
  expiry_date: string;
  batch_number: string;
  lot_number: string;
  description: string;
  indications: string[];
  contraindications: string[];
  side_effects: string[];
  storage_conditions: string;
  prescription_required: boolean;
  controlled_substance: boolean;
  category: string;
}

export default function MedsSamplesView() {
  const [visits, setVisits] = useState<MedrepVisit[]>([]);
  const [samples, setSamples] = useState<MedSample[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterCompany, setFilterCompany] = useState<string>('all');
  const [filterCategory, setFilterCategory] = useState<string>('all');
  const [activeTab, setActiveTab] = useState('visits');
  const [selectedSample, setSelectedSample] = useState<MedSample | null>(null);

  useEffect(() => {
    fetchMedrepVisits();
    fetchAllSamples();
  }, []);

  const fetchMedrepVisits = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/v1/medrep-visits');
      const data = await response.json();
      setVisits(data.data || []);
    } catch (error) {
      console.error('Error fetching medrep visits:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchAllSamples = async () => {
    try {
      // This would be a custom endpoint to get all samples from all visits
      const response = await fetch('/api/v1/meds-samples');
      const data = await response.json();
      setSamples(data.data || []);
    } catch (error) {
      console.error('Error fetching samples:', error);
    }
  };

  const filteredVisits = visits.filter(visit => {
    const matchesSearch =
      visit.medrep_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      visit.medrep_company.toLowerCase().includes(searchTerm.toLowerCase()) ||
      visit.purpose.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesCompany = filterCompany === 'all' || visit.medrep_company === filterCompany;

    return matchesSearch && matchesCompany;
  });

  const filteredSamples = samples.filter(sample => {
    const matchesSearch =
      sample.medication_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      sample.generic_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      sample.manufacturer.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesCategory = filterCategory === 'all' || sample.category === filterCategory;

    return matchesSearch && matchesCategory;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'scheduled':
        return 'bg-blue-100 text-blue-800';
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      case 'in_progress':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getCategoryColor = (category: string) => {
    switch (category) {
      case 'antibiotic':
        return 'bg-blue-100 text-blue-800';
      case 'pain_management':
        return 'bg-red-100 text-red-800';
      case 'cardiovascular':
        return 'bg-green-100 text-green-800';
      case 'diabetes':
        return 'bg-yellow-100 text-yellow-800';
      case 'respiratory':
        return 'bg-purple-100 text-purple-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getExpiryStatus = (expiryDate: string) => {
    const expiry = new Date(expiryDate);
    const now = new Date();
    const diffTime = expiry.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
      return { status: 'expired', color: 'bg-red-100 text-red-800' };
    } else if (diffDays <= 30) {
      return { status: 'expiring', color: 'bg-yellow-100 text-yellow-800' };
    } else {
      return { status: 'valid', color: 'bg-green-100 text-green-800' };
    }
  };

  const uniqueCompanies = [...new Set(visits.map(visit => visit.medrep_company))];
  const uniqueCategories = [...new Set(samples.map(sample => sample.category))];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Medication Samples</h2>
          <p className="text-gray-600">Review medication samples from pharmaceutical representatives and manage sample inventory</p>
        </div>

        <div className="flex gap-2">
          <Button variant="outline" size="sm">
            <Download className="h-4 w-4 mr-2" />
            Export Samples
          </Button>
          <Button variant="outline" size="sm">
            <Calendar className="h-4 w-4 mr-2" />
            Schedule Visit
          </Button>
        </div>
      </div>

      {/* Search and Filters */}
      <div className="flex flex-col sm:flex-row gap-4">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
          <Input
            placeholder="Search by MedRep, company, medication, or purpose..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="pl-10"
          />
        </div>

        {activeTab === 'visits' && (
          <Select value={filterCompany} onValueChange={setFilterCompany}>
            <SelectTrigger className="w-[200px]">
              <SelectValue placeholder="Filter by company" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Companies</SelectItem>
              {uniqueCompanies.map(company => (
                <SelectItem key={company} value={company}>{company}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}

        {activeTab === 'samples' && (
          <Select value={filterCategory} onValueChange={setFilterCategory}>
            <SelectTrigger className="w-[200px]">
              <SelectValue placeholder="Filter by category" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Categories</SelectItem>
              {uniqueCategories.map(category => (
                <SelectItem key={category} value={category}>
                  {category.replace('_', ' ').toUpperCase()}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}
      </div>

      {/* Tabs */}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="visits">MedRep Visits</TabsTrigger>
          <TabsTrigger value="samples">All Samples</TabsTrigger>
        </TabsList>

        <TabsContent value="visits" className="mt-6">
          {/* MedRep Visits List */}
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
          ) : (
            <div className="space-y-4">
              {filteredVisits.length === 0 ? (
                <Card>
                  <CardContent className="flex flex-col items-center justify-center py-12">
                    <Calendar className="h-12 w-12 text-gray-400 mb-4" />
                    <h3 className="text-lg font-medium text-gray-900 mb-2">No MedRep visits found</h3>
                    <p className="text-gray-500 text-center">
                      {searchTerm ? 'No visits match your search criteria.' : 'No MedRep visits scheduled yet.'}
                    </p>
                  </CardContent>
                </Card>
              ) : (
                filteredVisits.map((visit) => (
                  <Card key={visit.id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-6">
                      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div className="flex-1">
                          <div className="flex items-center gap-3 mb-3">
                            <h4 className="text-lg font-semibold text-gray-900">
                              {visit.medrep_name}
                            </h4>
                            <Badge className={getStatusColor(visit.status)}>
                              {visit.status.replace('_', ' ')}
                            </Badge>
                            <Badge variant="outline">
                              {visit.samples_provided.length} samples
                            </Badge>
                          </div>

                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Building className="h-4 w-4" />
                                <span className="font-medium">Company:</span>
                                <span>{visit.medrep_company}</span>
                              </div>

                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Calendar className="h-4 w-4" />
                                <span className="font-medium">Date:</span>
                                <span>{new Date(visit.start_at).toLocaleDateString()}</span>
                              </div>

                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Clock className="h-4 w-4" />
                                <span className="font-medium">Time:</span>
                                <span>
                                  {new Date(visit.start_at).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                  })} - {new Date(visit.end_at).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                  })}
                                </span>
                              </div>
                            </div>

                            <div className="space-y-2">
                              {visit.medrep_contact.phone && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <Phone className="h-4 w-4" />
                                  <span>{visit.medrep_contact.phone}</span>
                                </div>
                              )}

                              {visit.medrep_contact.email && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <Mail className="h-4 w-4" />
                                  <span>{visit.medrep_contact.email}</span>
                                </div>
                              )}
                            </div>
                          </div>

                          <div className="mt-3">
                            <p className="text-sm text-gray-700">
                              <span className="font-medium">Purpose:</span> {visit.purpose}
                            </p>
                          </div>

                          {visit.notes && (
                            <div className="mt-3">
                              <p className="text-sm text-gray-700">
                                <span className="font-medium">Notes:</span> {visit.notes}
                              </p>
                            </div>
                          )}

                          {visit.samples_provided.length > 0 && (
                            <div className="mt-4">
                              <p className="text-sm font-medium text-gray-900 mb-2">Samples Provided:</p>
                              <div className="flex flex-wrap gap-2">
                                {visit.samples_provided.map((sample, index) => (
                                  <Badge key={index} variant="secondary" className="flex items-center gap-1">
                                    <Pill className="h-3 w-3" />
                                    {sample.medication_name}
                                  </Badge>
                                ))}
                              </div>
                            </div>
                          )}
                        </div>

                        <div className="flex flex-col gap-2">
                          <Button size="sm" variant="outline">
                            <Eye className="h-4 w-4 mr-2" />
                            View Details
                          </Button>

                          {visit.status === 'scheduled' && (
                            <Button size="sm" variant="outline">
                              Reschedule
                            </Button>
                          )}

                          {visit.samples_provided.length > 0 && (
                            <Button size="sm" variant="outline">
                              <Download className="h-4 w-4 mr-2" />
                              Download List
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

        <TabsContent value="samples" className="mt-6">
          {/* All Samples List */}
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredSamples.map((sample) => {
                const expiryStatus = getExpiryStatus(sample.expiry_date);
                return (
                  <Card
                    key={sample.id}
                    className="hover:shadow-md transition-shadow cursor-pointer"
                    onClick={() => setSelectedSample(sample)}
                  >
                    <CardHeader className="pb-3">
                      <div className="flex items-start justify-between">
                        <div className="flex-1">
                          <CardTitle className="text-lg">{sample.medication_name}</CardTitle>
                          <CardDescription>{sample.generic_name}</CardDescription>
                        </div>
                        <Badge className={getCategoryColor(sample.category)}>
                          {sample.category.replace('_', ' ')}
                        </Badge>
                      </div>
                    </CardHeader>

                    <CardContent className="space-y-3">
                      <div className="space-y-2">
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <Building className="h-4 w-4" />
                          <span>{sample.manufacturer}</span>
                        </div>

                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <Package className="h-4 w-4" />
                          <span>{sample.strength} {sample.dosage_form}</span>
                        </div>

                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <span className="font-medium">Quantity:</span>
                          <span>{sample.quantity} units</span>
                        </div>

                        <div className="flex items-center gap-2 text-sm text-gray-600">
                          <Calendar className="h-4 w-4" />
                          <span>Expires: {new Date(sample.expiry_date).toLocaleDateString()}</span>
                        </div>
                      </div>

                      <div className="flex items-center justify-between">
                        <Badge className={expiryStatus.color}>
                          {expiryStatus.status}
                        </Badge>

                        {sample.controlled_substance && (
                          <Badge variant="destructive" className="flex items-center gap-1">
                            <AlertTriangle className="h-3 w-3" />
                            Controlled
                          </Badge>
                        )}
                      </div>

                      {sample.description && (
                        <p className="text-sm text-gray-600 line-clamp-2">
                          {sample.description}
                        </p>
                      )}

                      <div className="flex gap-2 pt-2">
                        <Button
                          size="sm"
                          variant="outline"
                          className="flex-1"
                          onClick={(e) => {
                            e.stopPropagation();
                            setSelectedSample(sample);
                          }}
                        >
                          <Eye className="h-4 w-4 mr-2" />
                          View Details
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          )}

          {filteredSamples.length === 0 && !loading && (
            <Card>
              <CardContent className="flex flex-col items-center justify-center py-12">
                <Pill className="h-12 w-12 text-gray-400 mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No samples found</h3>
                <p className="text-gray-500 text-center">
                  {searchTerm ? 'No samples match your search criteria.' : 'No medication samples available yet.'}
                </p>
              </CardContent>
            </Card>
          )}
        </TabsContent>
      </Tabs>

      {/* Sample Details Dialog */}
      {selectedSample && (
        <Dialog open={!!selectedSample} onOpenChange={() => setSelectedSample(null)}>
          <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Sample Details</DialogTitle>
              <DialogDescription>
                {selectedSample.medication_name} - {selectedSample.manufacturer}
              </DialogDescription>
            </DialogHeader>
            <SampleDetails sample={selectedSample} />
          </DialogContent>
        </Dialog>
      )}
    </div>
  );
}

// Sample Details Component
function SampleDetails({ sample }: { sample: MedSample }) {
  const expiryStatus = getExpiryStatus(sample.expiry_date);

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Medication Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <span className="font-medium">Brand Name:</span> {sample.medication_name}
            </div>
            <div>
              <span className="font-medium">Generic Name:</span> {sample.generic_name}
            </div>
            <div>
              <span className="font-medium">Manufacturer:</span> {sample.manufacturer}
            </div>
            <div>
              <span className="font-medium">Dosage Form:</span> {sample.dosage_form}
            </div>
            <div>
              <span className="font-medium">Strength:</span> {sample.strength}
            </div>
            <div>
              <span className="font-medium">Category:</span>
              <Badge className={getCategoryColor(sample.category)}>
                {sample.category.replace('_', ' ')}
              </Badge>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Sample Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <span className="font-medium">Quantity:</span> {sample.quantity} units
            </div>
            <div>
              <span className="font-medium">Batch Number:</span> {sample.batch_number}
            </div>
            <div>
              <span className="font-medium">Lot Number:</span> {sample.lot_number}
            </div>
            <div>
              <span className="font-medium">Expiry Date:</span> {new Date(sample.expiry_date).toLocaleDateString()}
            </div>
            <div>
              <span className="font-medium">Status:</span>
              <Badge className={expiryStatus.color}>
                {expiryStatus.status}
              </Badge>
            </div>
            <div>
              <span className="font-medium">Storage:</span> {sample.storage_conditions}
            </div>
          </CardContent>
        </Card>
      </div>

      {sample.description && (
        <Card>
          <CardHeader>
            <CardTitle>Description</CardTitle>
          </CardHeader>
          <CardContent>
            <p>{sample.description}</p>
          </CardContent>
        </Card>
      )}

      {sample.indications.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Indications</CardTitle>
          </CardHeader>
          <CardContent>
            <ul className="list-disc list-inside space-y-1">
              {sample.indications.map((indication, index) => (
                <li key={index}>{indication}</li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}

      {sample.contraindications.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Contraindications</CardTitle>
          </CardHeader>
          <CardContent>
            <ul className="list-disc list-inside space-y-1">
              {sample.contraindications.map((contraindication, index) => (
                <li key={index}>{contraindication}</li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}

      {sample.side_effects.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Side Effects</CardTitle>
          </CardHeader>
          <CardContent>
            <ul className="list-disc list-inside space-y-1">
              {sample.side_effects.map((effect, index) => (
                <li key={index}>{effect}</li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}

      <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
        <div className="flex items-center gap-2">
          {sample.prescription_required ? (
            <CheckCircle className="h-5 w-5 text-red-600" />
          ) : (
            <CheckCircle className="h-5 w-5 text-green-600" />
          )}
          <span className="font-medium">
            {sample.prescription_required ? 'Prescription Required' : 'Over the Counter'}
          </span>
        </div>

        {sample.controlled_substance && (
          <div className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-orange-600" />
            <span className="font-medium text-orange-600">Controlled Substance</span>
          </div>
        )}
      </div>
    </div>
  );
}

// Helper functions
function getCategoryColor(category: string) {
  switch (category) {
    case 'antibiotic':
      return 'bg-blue-100 text-blue-800';
    case 'pain_management':
      return 'bg-red-100 text-red-800';
    case 'cardiovascular':
      return 'bg-green-100 text-green-800';
    case 'diabetes':
      return 'bg-yellow-100 text-yellow-800';
    case 'respiratory':
      return 'bg-purple-100 text-purple-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

function getExpiryStatus(expiryDate: string) {
  const expiry = new Date(expiryDate);
  const now = new Date();
  const diffTime = expiry.getTime() - now.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays < 0) {
    return { status: 'expired', color: 'bg-red-100 text-red-800' };
  } else if (diffDays <= 30) {
    return { status: 'expiring', color: 'bg-yellow-100 text-yellow-800' };
  } else {
    return { status: 'valid', color: 'bg-green-100 text-green-800' };
  }
}
