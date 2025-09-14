# MediNext Seeder Command Guide

## Overview

The `MediNextSeeder` command provides a flexible way to run the BaseSeeder with options to select specific seeds or steps. This allows for targeted database seeding during development, testing, or production setup.

## Command Signature

```bash
php artisan medinext:seed [options]
```

## Available Options

### Step Selection
- `--step=*` : Run specific seeding steps (core, infrastructure, users, business, activity)

### Method Selection
- `--method=*` : Run specific individual methods (permissions, roles, settings, clinics, rooms, nova-admin, demo-users, doctors, patients, appointments, encounters, prescriptions, lab-results, bills, insurance, queue, notifications, activity-logs)

### Configuration Options
- `--skip-existing` : Skip creating records that already exist
- `--validate-data` : Validate data before inserting
- `--show-progress` : Show progress during seeding
- `--memory-optimized` : Use memory optimization
- `--create-demo-data` : Create demo data

### Utility Options
- `--list` : List all available seeding options
- `--interactive` : Interactive mode to select options

## Available Seeding Steps

### 1. Core System (`core`)
**Description**: Permissions, roles, and default settings
**Methods**: permissions, roles, settings

### 2. Infrastructure (`infrastructure`)
**Description**: Clinics and rooms
**Methods**: clinics, rooms

### 3. Users and Roles (`users`)
**Description**: Nova admin, demo users, and doctors
**Methods**: nova-admin, demo-users, doctors

### 4. Business Data (`business`)
**Description**: Patients, appointments, encounters, prescriptions, lab results, bills, insurance, queue, notifications
**Methods**: patients, appointments, encounters, prescriptions, lab-results, bills, insurance, queue, notifications

### 5. Activity Logs (`activity`)
**Description**: System activity logs
**Methods**: activity-logs

## Available Individual Methods

| Method | Description |
|--------|-------------|
| `permissions` | Create system permissions |
| `roles` | Create user roles |
| `settings` | Create default settings |
| `clinics` | Create clinics |
| `rooms` | Create rooms |
| `nova-admin` | Create Nova admin user |
| `demo-users` | Create demo users |
| `doctors` | Create doctors |
| `patients` | Create patients |
| `appointments` | Create appointments |
| `encounters` | Create encounters |
| `prescriptions` | Create prescriptions |
| `lab-results` | Create lab results |
| `bills` | Create bills |
| `insurance` | Create insurance records |
| `queue` | Create queue data |
| `notifications` | Create notifications |
| `activity-logs` | Create activity logs |

## Usage Examples

### 1. List Available Options
```bash
php artisan medinext:seed --list
```

### 2. Run Full Seeder (Default)
```bash
php artisan medinext:seed
```

### 3. Run Specific Steps
```bash
# Run core system and infrastructure
php artisan medinext:seed --step=core --step=infrastructure

# Run only business data
php artisan medinext:seed --step=business
```

### 4. Run Specific Methods
```bash
# Run only permissions and roles
php artisan medinext:seed --method=permissions --method=roles

# Run only clinics and rooms
php artisan medinext:seed --method=clinics --method=rooms
```

### 5. Run with Configuration Options
```bash
# Run with skip existing and show progress
php artisan medinext:seed --step=core --skip-existing --show-progress

# Run with demo data creation
php artisan medinext:seed --step=business --create-demo-data
```

### 6. Interactive Mode
```bash
php artisan medinext:seed --interactive
```

### 7. Mixed Step and Method Selection
```bash
# Run core step plus specific methods
php artisan medinext:seed --step=core --method=patients --method=appointments
```

## Interactive Mode

When using `--interactive`, the command will guide you through the selection process:

1. **Seeding Type Selection**: Choose between Full Seeder, Specific Steps, Specific Methods, or Custom Selection
2. **Step Selection**: If choosing specific steps, select from available steps
3. **Method Selection**: If choosing specific methods, select from available methods
4. **Custom Selection**: Manually enter steps and/or methods

## Development Workflows

### Initial Setup
```bash
# Full system setup
php artisan medinext:seed

# Or step by step
php artisan medinext:seed --step=core
php artisan medinext:seed --step=infrastructure
php artisan medinext:seed --step=users
```

### Testing Specific Features
```bash
# Test patient management
php artisan medinext:seed --method=patients --method=appointments

# Test billing system
php artisan medinext:seed --method=bills --method=insurance
```

### Adding Demo Data
```bash
# Add demo data to existing system
php artisan medinext:seed --step=business --create-demo-data
```

### Reset Specific Data
```bash
# Reset and recreate core system
php artisan medinext:seed --step=core --skip-existing=false
```

## Error Handling

The command includes comprehensive error handling:

- **Validation**: Invalid steps or methods are caught and reported
- **Transaction Safety**: All operations run within database transactions
- **Rollback**: Failed operations are automatically rolled back
- **Memory Management**: Automatic garbage collection and memory optimization
- **Progress Tracking**: Real-time progress reporting for long operations

## Performance Considerations

### Memory Optimization
- Automatic memory limit increase to 2GB
- Garbage collection enabled
- Memory-optimized option for large datasets

### Transaction Management
- All operations wrapped in database transactions
- Automatic rollback on failure
- Progress tracking and error reporting

### Skip Existing Records
- Option to skip records that already exist
- Prevents duplicate data creation
- Useful for incremental updates

## Integration with Existing Commands

The MediNextSeeder command works alongside existing MediNext commands:

```bash
# Complete system setup
php artisan medinext:setup
php artisan medinext:seed --step=core
php artisan medinext:permissions
php artisan medinext:seed --step=business
```

## Troubleshooting

### Common Issues

1. **Memory Exhaustion**
   ```bash
   # Use memory optimization
   php artisan medinext:seed --memory-optimized
   ```

2. **Duplicate Records**
   ```bash
   # Skip existing records
   php artisan medinext:seed --skip-existing
   ```

3. **Validation Errors**
   ```bash
   # Enable data validation
   php artisan medinext:seed --validate-data
   ```

### Debug Mode
```bash
# Run with verbose output
php artisan medinext:seed --step=core --show-progress
```

## Best Practices

1. **Development**: Use specific steps/methods for targeted testing
2. **Testing**: Use `--skip-existing` to avoid duplicate data
3. **Production**: Always use `--validate-data` for data integrity
4. **Performance**: Use `--memory-optimized` for large datasets
5. **Debugging**: Use `--show-progress` to monitor long operations

## Examples for Common Scenarios

### Fresh Installation
```bash
php artisan medinext:seed
```

### Add Demo Data to Existing System
```bash
php artisan medinext:seed --step=business --create-demo-data --skip-existing
```

### Reset Permissions and Roles
```bash
php artisan medinext:seed --method=permissions --method=roles
```

### Test Patient Workflow
```bash
php artisan medinext:seed --method=patients --method=appointments --method=encounters
```

### Quick Development Setup
```bash
php artisan medinext:seed --step=core --step=infrastructure --step=users
```

This command provides the flexibility needed for various development, testing, and production scenarios while maintaining the robustness and safety of the original BaseSeeder.
