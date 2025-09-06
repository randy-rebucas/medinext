# Frontend Implementation Guide - Receptionist & Doctor Use Cases

This document provides a comprehensive guide to the frontend implementation for the receptionist and doctor use cases in the MediNext EMR system.

## Overview

The frontend is built using:
- **React 18** with TypeScript
- **Inertia.js** for seamless Laravel-React integration
- **Tailwind CSS** for styling
- **shadcn/ui** components for consistent UI
- **Lucide React** for icons

## File Structure

```
resources/js/
├── pages/
│   ├── receptionist/
│   │   └── dashboard.tsx          # Main receptionist dashboard
│   └── doctor/
│       └── queue/
│           └── index.tsx          # Doctor queue management
├── components/
│   ├── queue-management.tsx       # Reusable queue component
│   └── patient-search.tsx         # Reusable patient search
├── types/
│   └── index.ts                   # TypeScript interfaces
└── routes.ts                      # Frontend routing utilities
```

## Features Implemented

### 1. Receptionist Dashboard (`/receptionist/dashboard`)

**Main Features:**
- Patient search by name or ID
- New patient registration
- Encounter creation
- Queue management
- Real-time statistics

**Key Components:**
- **Patient Search**: Search existing patients with real-time results
- **New Patient Form**: Complete patient registration with validation
- **Encounter Form**: Create new encounters with visit type and reason
- **Queue Overview**: View active queue and recent encounters
- **Statistics Cards**: Display key metrics

**User Flow:**
1. Search for patient by name or ID
2. If found: Select patient → Create encounter → Add to queue
3. If not found: Register new patient → Create encounter → Add to queue

### 2. Doctor Queue Management (`/doctor/queue`)

**Main Features:**
- Active patient queue with priority indicators
- Clinical documentation interface
- SOAP notes management
- Vital signs recording
- Diagnosis and treatment planning
- Prescription management
- File attachments

**Key Components:**
- **Queue Display**: Priority-based patient queue with wait times
- **Clinical Documentation**: Comprehensive encounter documentation
- **SOAP Notes**: Subjective, Objective, Assessment, Plan sections
- **Vital Signs**: Blood pressure, heart rate, temperature, weight, height
- **Diagnosis Management**: ICD-10 codes and treatment plans
- **Prescription Interface**: Medication management
- **File Management**: Upload lab results, X-rays, reports

**User Flow:**
1. View active queue with patient priorities
2. Select patient to start encounter
3. Complete clinical documentation
4. Record diagnosis and treatment plan
5. Generate prescriptions and reports
6. Complete encounter

### 3. Reusable Components

#### Queue Management Component
- **Location**: `resources/js/components/queue-management.tsx`
- **Features**:
  - Real-time queue updates
  - Patient filtering and search
  - Queue position management
  - Status tracking
  - Role-based actions (receptionist vs doctor)

#### Patient Search Component
- **Location**: `resources/js/components/patient-search.tsx`
- **Features**:
  - Real-time search with debouncing
  - Patient details display
  - New patient registration
  - Medical history overview
  - Allergy and contact information

## API Integration

### Authentication
All API calls include Bearer token authentication:
```typescript
headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json',
}
```

### Key API Endpoints Used

#### Patient Management
- `GET /api/v1/patients/search` - Search patients
- `POST /api/v1/patients` - Create new patient
- `GET /api/v1/patients/{id}` - Get patient details

#### Encounter Management
- `POST /api/v1/encounters` - Create new encounter
- `PUT /api/v1/encounters/{id}` - Update encounter
- `POST /api/v1/encounters/{id}/complete` - Complete encounter
- `POST /api/v1/encounters/{id}/queue` - Add to queue

#### Queue Management
- `GET /api/v1/queue/active` - Get active queue
- `PUT /api/v1/queue/{id}/position` - Update queue position
- `DELETE /api/v1/queue/{id}` - Remove from queue

## TypeScript Interfaces

### Core Types
- **Patient**: Complete patient information
- **Encounter**: Medical encounter details
- **QueueItem**: Queue position and patient data
- **Appointment**: Scheduled appointments
- **Prescription**: Medication prescriptions

### Form Types
- **PatientFormData**: New patient registration
- **EncounterFormData**: Encounter creation
- **SoapNotesFormData**: Clinical documentation
- **VitalSignsFormData**: Patient vital signs

## State Management

### Local State
- Component-level state using React hooks
- Form data management
- Search results and filters
- Dialog/modal states

### Data Flow
1. User interactions trigger API calls
2. API responses update local state
3. UI re-renders with new data
4. Real-time updates via periodic refresh

## Error Handling

### API Errors
- Network error handling
- HTTP status code checking
- User-friendly error messages
- Retry mechanisms for failed requests

### Form Validation
- Client-side validation
- Required field checking
- Data format validation
- Real-time feedback

## Responsive Design

### Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

### Layout Adaptations
- Collapsible sidebars on mobile
- Stacked form layouts on small screens
- Responsive grid systems
- Touch-friendly button sizes

## Accessibility Features

### ARIA Labels
- Screen reader support
- Keyboard navigation
- Focus management
- Semantic HTML structure

### Visual Indicators
- Color-coded status badges
- Priority indicators
- Loading states
- Error states

## Performance Optimizations

### Code Splitting
- Route-based code splitting
- Lazy loading of components
- Dynamic imports for heavy components

### Data Fetching
- Debounced search inputs
- Pagination for large datasets
- Caching of frequently accessed data
- Optimistic updates

## Security Considerations

### Authentication
- JWT token management
- Automatic token refresh
- Secure storage of credentials
- Session timeout handling

### Data Protection
- Input sanitization
- XSS prevention
- CSRF protection via Laravel
- Secure API communication

## Testing Strategy

### Unit Tests
- Component testing with React Testing Library
- Hook testing
- Utility function testing

### Integration Tests
- API integration testing
- User flow testing
- Cross-browser compatibility

## Deployment Considerations

### Build Process
- TypeScript compilation
- CSS optimization
- Asset bundling
- Code minification

### Environment Configuration
- API endpoint configuration
- Feature flags
- Debug mode settings
- Performance monitoring

## Future Enhancements

### Planned Features
- Real-time notifications
- Advanced search filters
- Bulk operations
- Export functionality
- Mobile app integration

### Performance Improvements
- Virtual scrolling for large lists
- Service worker implementation
- Offline functionality
- Progressive Web App features

## Usage Examples

### Basic Patient Search
```typescript
import { PatientSearch } from '@/components/patient-search';

function MyComponent() {
    const handlePatientSelect = (patient: Patient) => {
        console.log('Selected patient:', patient);
    };

    return (
        <PatientSearch 
            onPatientSelect={handlePatientSelect}
            showNewPatientButton={true}
        />
    );
}
```

### Queue Management
```typescript
import { QueueManagement } from '@/components/queue-management';

function DoctorQueue() {
    const handlePatientSelect = (patient: QueueItem) => {
        // Start encounter
    };

    return (
        <QueueManagement
            initialQueue={queueData}
            userRole="doctor"
            onPatientSelect={handlePatientSelect}
        />
    );
}
```

## Troubleshooting

### Common Issues
1. **API Connection Errors**: Check network connectivity and API endpoints
2. **Authentication Failures**: Verify token validity and refresh logic
3. **Form Validation**: Ensure all required fields are properly validated
4. **Performance Issues**: Check for unnecessary re-renders and API calls

### Debug Tools
- React Developer Tools
- Browser Network tab
- Console logging
- Error boundary components

## Support and Maintenance

### Code Organization
- Consistent naming conventions
- Comprehensive TypeScript types
- Modular component structure
- Clear separation of concerns

### Documentation
- Inline code comments
- Component prop documentation
- API integration guides
- User flow documentation

This implementation provides a solid foundation for the receptionist and doctor use cases, with room for future enhancements and scalability.
