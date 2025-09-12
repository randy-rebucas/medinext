import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Download, FileText, Printer } from 'lucide-react';
import { type Prescription, type Encounter, type Patient } from '@/types';

interface PDFGeneratorProps {
    type: 'prescription' | 'medical-report';
    data: Prescription | Encounter;
    patient?: Patient;
    onDownload?: (url: string) => void;
}

export function PDFGenerator({ type, data, onDownload }: PDFGeneratorProps) {
    const [isGenerating, setIsGenerating] = useState(false);

    const generatePDF = async () => {
        setIsGenerating(true);
        try {
            let endpoint = '';
            let filename = '';

            if (type === 'prescription') {
                endpoint = `/api/v1/prescriptions/${(data as Prescription).id}/pdf`;
                filename = `prescription-${(data as Prescription).prescription_number}.pdf`;
            } else {
                endpoint = `/api/v1/encounters/${(data as Encounter).id}/medical-report`;
                filename = `medical-report-${(data as Encounter).encounter_number}.pdf`;
            }

            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/pdf',
                },
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);

                onDownload?.(url);
            }
        } catch (error) {
            console.error('Error generating PDF:', error);
        } finally {
            setIsGenerating(false);
        }
    };

    const printPDF = async () => {
        setIsGenerating(true);
        try {
            let endpoint = '';

            if (type === 'prescription') {
                endpoint = `/api/v1/prescriptions/${(data as Prescription).id}/pdf`;
            } else {
                endpoint = `/api/v1/encounters/${(data as Encounter).id}/medical-report`;
            }

            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/pdf',
                },
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const printWindow = window.open(url, '_blank');
                if (printWindow) {
                    printWindow.onload = () => {
                        printWindow.print();
                    };
                }
            }
        } catch (error) {
            console.error('Error printing PDF:', error);
        } finally {
            setIsGenerating(false);
        }
    };

    return (
        <div className="flex gap-2">
            <Button
                size="sm"
                onClick={generatePDF}
                disabled={isGenerating}
            >
                <Download className="h-4 w-4 mr-2" />
                {isGenerating ? 'Generating...' : 'Download PDF'}
            </Button>
            <Button
                size="sm"
                variant="outline"
                onClick={printPDF}
                disabled={isGenerating}
            >
                <Printer className="h-4 w-4 mr-2" />
                Print
            </Button>
        </div>
    );
}

// Prescription PDF Preview Component
export function PrescriptionPDFPreview({ prescription, patient }: { prescription: Prescription; patient: Patient }) {
    return (
        <div className="bg-white p-8 max-w-2xl mx-auto shadow-lg">
            {/* Header */}
            <div className="text-center border-b pb-4 mb-6">
                <h1 className="text-2xl font-bold text-gray-800">PRESCRIPTION</h1>
                <p className="text-sm text-gray-600">Prescription #{prescription.prescription_number}</p>
            </div>

            {/* Patient Information */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">Patient Information</h2>
                <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p><strong>Name:</strong> {patient.name}</p>
                        <p><strong>Patient ID:</strong> {patient.patient_id}</p>
                        <p><strong>DOB:</strong> {new Date(patient.dob).toLocaleDateString()}</p>
                    </div>
                    <div>
                        <p><strong>Sex:</strong> {patient.sex}</p>
                        <p><strong>Phone:</strong> {patient.contact.phone || 'N/A'}</p>
                        <p><strong>Email:</strong> {patient.contact.email || 'N/A'}</p>
                    </div>
                </div>
            </div>

            {/* Doctor Information */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">Prescribing Doctor</h2>
                <div className="text-sm">
                    <p><strong>Doctor:</strong> {prescription.doctor_name}</p>
                    <p><strong>Date:</strong> {new Date(prescription.issued_at).toLocaleDateString()}</p>
                </div>
            </div>

            {/* Diagnosis */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">Diagnosis</h2>
                <p className="text-sm">{prescription.diagnosis}</p>
            </div>

            {/* Medications */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">Medications</h2>
                <div className="space-y-3">
                    {prescription.items.map((item, index) => (
                        <div key={index} className="border-l-4 border-blue-500 pl-4">
                            <div className="flex justify-between items-start">
                                <div>
                                    <p className="font-semibold">{item.medication_name}</p>
                                    <p className="text-sm text-gray-600">
                                        {item.dosage} - {item.frequency} for {item.duration}
                                    </p>
                                    <p className="text-sm text-gray-600">Quantity: {item.quantity}</p>
                                    {item.instructions && (
                                        <p className="text-sm text-gray-600 mt-1">
                                            <strong>Instructions:</strong> {item.instructions}
                                        </p>
                                    )}
                                </div>
                                <div className="text-right">
                                    <p className="text-sm font-semibold">${item.cost}</p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Instructions */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">General Instructions</h2>
                <p className="text-sm whitespace-pre-wrap">{prescription.instructions}</p>
            </div>

            {/* Footer */}
            <div className="border-t pt-4 mt-6">
                <div className="flex justify-between items-center text-sm">
                    <div>
                        <p><strong>Total Cost:</strong> ${prescription.total_cost}</p>
                        <p><strong>Refills Allowed:</strong> {prescription.refills_allowed}</p>
                        <p><strong>Refills Remaining:</strong> {prescription.refills_remaining}</p>
                    </div>
                    <div className="text-right">
                        <p><strong>Expiry Date:</strong> {new Date(prescription.expiry_date).toLocaleDateString()}</p>
                        <p className="mt-2 text-xs text-gray-500">
                            Generated on {new Date().toLocaleDateString()}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

// Medical Report PDF Preview Component
export function MedicalReportPDFPreview({ encounter, patient }: { encounter: Encounter; patient: Patient }) {
    return (
        <div className="bg-white p-8 max-w-4xl mx-auto shadow-lg">
            {/* Header */}
            <div className="text-center border-b pb-4 mb-6">
                <h1 className="text-2xl font-bold text-gray-800">MEDICAL REPORT</h1>
                <p className="text-sm text-gray-600">Encounter #{encounter.encounter_number}</p>
            </div>

            {/* Patient Information */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">Patient Information</h2>
                <div className="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p><strong>Name:</strong> {patient.name}</p>
                        <p><strong>Patient ID:</strong> {patient.patient_id}</p>
                        <p><strong>DOB:</strong> {new Date(patient.dob).toLocaleDateString()}</p>
                    </div>
                    <div>
                        <p><strong>Sex:</strong> {patient.sex}</p>
                        <p><strong>Phone:</strong> {patient.contact.phone || 'N/A'}</p>
                        <p><strong>Email:</strong> {patient.contact.email || 'N/A'}</p>
                    </div>
                    <div>
                        <p><strong>Visit Type:</strong> {encounter.visit_type}</p>
                        <p><strong>Date:</strong> {new Date(encounter.created_at).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> {encounter.status}</p>
                    </div>
                </div>
            </div>

            {/* Chief Complaint */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-3 text-gray-800">Chief Complaint</h2>
                <p className="text-sm">{encounter.reason_for_visit}</p>
            </div>

            {/* SOAP Notes */}
            {encounter.soap_notes && (
                <div className="mb-6">
                    <h2 className="text-lg font-semibold mb-3 text-gray-800">SOAP Notes</h2>
                    <div className="space-y-4">
                        <div>
                            <h3 className="font-semibold text-gray-700">Subjective</h3>
                            <p className="text-sm">{encounter.soap_notes.subjective}</p>
                        </div>
                        <div>
                            <h3 className="font-semibold text-gray-700">Objective</h3>
                            <p className="text-sm">{encounter.soap_notes.objective}</p>
                        </div>
                        <div>
                            <h3 className="font-semibold text-gray-700">Assessment</h3>
                            <p className="text-sm">{encounter.soap_notes.assessment}</p>
                        </div>
                        <div>
                            <h3 className="font-semibold text-gray-700">Plan</h3>
                            <p className="text-sm">{encounter.soap_notes.plan}</p>
                        </div>
                    </div>
                </div>
            )}

            {/* Vital Signs */}
            {encounter.vital_signs && (
                <div className="mb-6">
                    <h2 className="text-lg font-semibold mb-3 text-gray-800">Vital Signs</h2>
                    <div className="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p><strong>Blood Pressure:</strong> {encounter.vital_signs.blood_pressure}</p>
                            <p><strong>Heart Rate:</strong> {encounter.vital_signs.heart_rate} BPM</p>
                        </div>
                        <div>
                            <p><strong>Temperature:</strong> {encounter.vital_signs.temperature}°F</p>
                            <p><strong>Weight:</strong> {encounter.vital_signs.weight} lbs</p>
                        </div>
                        <div>
                            <p><strong>Height:</strong> {encounter.vital_signs.height} inches</p>
                        </div>
                    </div>
                </div>
            )}

            {/* Diagnosis */}
            {encounter.diagnosis && encounter.diagnosis.length > 0 && (
                <div className="mb-6">
                    <h2 className="text-lg font-semibold mb-3 text-gray-800">Diagnosis</h2>
                    <div className="space-y-2">
                        {encounter.diagnosis.map((diag, index) => (
                            <div key={index} className="flex items-center gap-2">
                                <span className="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs">
                                    {index + 1}
                                </span>
                                <span className="text-sm">{diag}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Treatment Plan */}
            {encounter.treatment_plan && (
                <div className="mb-6">
                    <h2 className="text-lg font-semibold mb-3 text-gray-800">Treatment Plan</h2>
                    <p className="text-sm whitespace-pre-wrap">{encounter.treatment_plan}</p>
                </div>
            )}

            {/* Follow-up */}
            {encounter.follow_up_date && (
                <div className="mb-6">
                    <h2 className="text-lg font-semibold mb-3 text-gray-800">Follow-up</h2>
                    <p className="text-sm">Next appointment scheduled for: {new Date(encounter.follow_up_date).toLocaleDateString()}</p>
                </div>
            )}

            {/* Footer */}
            <div className="border-t pt-4 mt-6">
                <div className="flex justify-between items-center text-sm">
                    <div>
                        <p><strong>Payment Status:</strong> {encounter.payment_status}</p>
                    </div>
                    <div className="text-right">
                        <p className="text-xs text-gray-500">
                            Generated on {new Date().toLocaleDateString()}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
