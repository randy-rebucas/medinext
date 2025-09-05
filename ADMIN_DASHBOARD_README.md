# Doctor Dashboard - MediNext

A comprehensive React-based doctor dashboard for medical professionals to manage their clinical practice efficiently.

## Features

### 1. View Own Schedule
- **Location**: `/doctor/dashboard?tab=schedule`
- **Component**: `ScheduleView.tsx`
- **Features**:
  - View appointments by day, week, or month
  - Filter appointments by status
  - Navigate through dates
  - View appointment details including patient info, time, room, and notes
  - Quick actions: Check-in, reschedule, complete appointments
  - Real-time status updates

### 2. Manage Assigned Patients' EMR
- **Location**: `/doctor/dashboard?tab=emr`
- **Component**: `EMRManagement.tsx`
- **Features**:
  - Search and filter patients
  - View patient contact information and medical details
  - Access complete medical history
  - View encounters with SOAP notes
  - Track prescriptions and lab results
  - Patient status management (active, inactive, deceased)
  - Allergy tracking and warnings

### 3. Issue Prescriptions
- **Location**: `/doctor/dashboard?tab=prescriptions`
- **Component**: `PrescriptionManager.tsx`
- **Features**:
  - Create new prescriptions
  - View all prescriptions with filtering
  - Prescription status tracking (draft, active, dispensed, expired)
  - Verification workflow
  - Prescription types: new, refill, emergency, controlled substances
  - Patient and medication information
  - PDF generation and QR codes
  - Refill management

### 4. View Meds Samples (from MedRep)
- **Location**: `/doctor/dashboard?tab=meds-samples`
- **Component**: `MedsSamplesView.tsx`
- **Features**:
  - View MedRep visits and schedules
  - Browse medication samples by category
  - Sample information including:
    - Medication details (brand/generic names, manufacturer)
    - Dosage forms and strengths
    - Expiry dates and batch numbers
    - Indications and contraindications
    - Side effects and warnings
    - Storage conditions
  - Filter by company, category, and expiry status
  - Controlled substance tracking

## Technical Implementation

### Architecture
- **Frontend**: React with TypeScript
- **UI Framework**: Tailwind CSS with Radix UI components
- **State Management**: React hooks (useState, useEffect)
- **Routing**: Inertia.js with Laravel backend
- **API Integration**: RESTful API endpoints

### File Structure
```
resources/js/
├── pages/admin/
│   ├── dashboard.tsx          # Main dashboard page
│   └── index.tsx             # Admin landing page
├── components/admin/
│   ├── ScheduleView.tsx      # Schedule management
│   ├── EMRManagement.tsx     # Patient EMR management
│   ├── PrescriptionManager.tsx # Prescription management
│   ├── MedsSamplesView.tsx   # Medication samples
│   └── AdminNavigation.tsx   # Navigation component
└── components/ui/
    └── tabs.tsx              # Tab component
```

### API Endpoints Used
- `GET /api/v1/appointments` - Fetch appointments
- `GET /api/v1/patients` - Fetch patients
- `GET /api/v1/prescriptions` - Fetch prescriptions
- `GET /api/v1/medrep-visits` - Fetch MedRep visits
- `GET /api/v1/meds-samples` - Fetch medication samples

### Key Components

#### Dashboard Overview
- Statistics cards showing key metrics
- Quick action buttons for common tasks
- Recent activity feed
- Upcoming tasks and reminders

#### Schedule View
- Calendar navigation (day/week/month views)
- Appointment cards with patient details
- Status-based filtering
- Real-time updates

#### EMR Management
- Patient search and filtering
- Medical history timeline
- Encounter details with SOAP notes
- File attachments and lab results

#### Prescription Manager
- Prescription creation workflow
- Status tracking and verification
- PDF generation
- Refill management

#### Meds Samples
- MedRep visit scheduling
- Sample inventory management
- Expiry tracking and alerts
- Detailed medication information

## Usage

### Accessing the Dashboard
1. Navigate to `/doctor` for the landing page
2. Click "Open Doctor Dashboard" or use quick access cards
3. Use tabs to switch between different clinical features

### Navigation
- **Overview Tab**: Clinical dashboard summary and quick actions
- **My Schedule Tab**: Patient appointment management
- **Patient Records Tab**: Medical records and EMR
- **Prescriptions Tab**: Prescription management
- **Med Samples Tab**: Medication samples from pharmaceutical reps

### Responsive Design
The dashboard is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile devices

## Future Enhancements

### Planned Features
- Real-time notifications
- Advanced search and filtering
- Bulk operations
- Export functionality
- Integration with external systems
- Mobile app support
- Offline capabilities

### Technical Improvements
- State management with Redux/Zustand
- Caching with React Query
- Performance optimization
- Accessibility improvements
- Unit and integration tests

## Dependencies

### Core Dependencies
- React 19.0.0
- TypeScript 5.7.2
- Tailwind CSS 4.0.0
- Inertia.js 2.1.0

### UI Components
- Radix UI components
- Lucide React icons
- Headless UI

### Development Tools
- Vite 7.0.4
- ESLint
- Prettier

## Getting Started

1. Ensure the Laravel backend is running
2. Install dependencies: `npm install`
3. Build assets: `npm run build`
4. Access the dashboard at `/doctor/dashboard`

## Support

For technical support or feature requests, please refer to the main project documentation or contact the development team.
