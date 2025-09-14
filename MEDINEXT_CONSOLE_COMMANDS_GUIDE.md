# MediNext EMR Console Commands Guide

## Overview

MediNext EMR now features a unified set of console commands with consistent naming and functionality. All commands follow the `medinext:` prefix for easy identification and management.

## Command Structure

All MediNext commands follow this pattern:
```bash
php artisan medinext:{command} {action} [options]
```

## Available Commands

### 1. MediNext Setup (`medinext:setup`)

Complete system setup with database migration and seeding.

```bash
php artisan medinext:setup [options]
```

**Options:**
- `--fresh` - Drop all tables and recreate them
- `--force` - Force operation without confirmation
- `--demo` - Include demo data in setup
- `--memory=2G` - Memory limit for seeding

**Examples:**
```bash
# Fresh setup with demo data
php artisan medinext:setup --fresh --demo

# Force setup without confirmation
php artisan medinext:setup --fresh --force

# Setup with custom memory limit
php artisan medinext:setup --memory=4G
```

**What it does:**
- Runs fresh migrations (if --fresh specified)
- Seeds the database with BaseSeeder
- Creates default users, roles, and permissions
- Sets up sample clinics and demo data
- Displays login information and next steps

### 2. MediNext Data (`medinext:data`)

Manage system data (clear, reset, status).

```bash
php artisan medinext:data {action} [options]
```

**Actions:**
- `clear` - Clear data from database
- `reset` - Clear and reseed data
- `status` - Show data status

**Options:**
- `--force` - Force operation without confirmation
- `--clinic=slug` - Target specific clinic

**Examples:**
```bash
# Clear all data
php artisan medinext:data clear --force

# Clear specific clinic data
php artisan medinext:data clear --clinic=demo-medical-center

# Reset all data (clear + reseed)
php artisan medinext:data reset --force

# Show data status
php artisan medinext:data status
```

**What it does:**
- **Clear**: Removes data while preserving structure
- **Reset**: Clears data and reseeds with BaseSeeder
- **Status**: Shows counts of users, clinics, patients, appointments, etc.

### 3. MediNext Permissions (`medinext:permissions`)

Manage permissions and roles system.

```bash
php artisan medinext:permissions {action} [options]
```

**Actions:**
- `update` - Update role permissions
- `validate` - Validate permissions system
- `fix` - Fix permission issues
- `status` - Show permissions status

**Options:**
- `--role=name` - Target specific role
- `--force` - Force update without confirmation
- `--fix` - Fix issues automatically

**Examples:**
```bash
# Update all role permissions
php artisan medinext:permissions update --force

# Update specific role
php artisan medinext:permissions update --role=admin --force

# Validate permissions system
php artisan medinext:permissions validate

# Validate and fix issues
php artisan medinext:permissions validate --fix

# Show permissions status
php artisan medinext:permissions status
```

**What it does:**
- **Update**: Assigns comprehensive permissions to roles
- **Validate**: Checks for missing permissions, orphaned permissions, etc.
- **Fix**: Updates all role permissions to latest configuration
- **Status**: Shows permission counts, role assignments, user access

### 4. MediNext License (`medinext:license`)

Manage license keys and validation.

```bash
php artisan medinext:license {action} [options]
```

**Actions:**
- `generate` - Generate license keys
- `validate` - Validate existing license keys
- `status` - Show license status

**Options:**
- `--count=1` - Number of keys to generate
- `--strategy=standard` - Generation strategy (standard, compact, segmented, custom)
- `--prefix=MEDI` - License key prefix
- `--output=file` - Save keys to file
- `--dry-run` - Show what would be generated
- `--validate` - Validate generated keys

**Examples:**
```bash
# Generate single license key
php artisan medinext:license generate

# Generate multiple keys
php artisan medinext:license generate --count=10

# Generate with custom strategy
php artisan medinext:license generate --strategy=compact --length=8

# Save keys to file
php artisan medinext:license generate --count=5 --output=licenses.txt

# Validate existing keys
php artisan medinext:license validate

# Show license status
php artisan medinext:license status
```

**What it does:**
- **Generate**: Creates license keys using various strategies
- **Validate**: Checks format and status of existing license keys
- **Status**: Shows license counts, types, and assignments

### 5. MediNext Access (`medinext:access`)

Manage user access and clinic assignments.

```bash
php artisan medinext:access {action} [options]
```

**Actions:**
- `setup` - Setup default access for all users
- `assign` - Assign specific user to clinic with role
- `list` - List all users, clinics, and roles
- `status` - Show access status

**Options:**
- `--user-email=email` - Target user email
- `--clinic-id=id` - Target clinic ID
- `--role=admin` - Role to assign
- `--force` - Force operation without confirmation

**Examples:**
```bash
# Setup default access for all users
php artisan medinext:access setup --force

# Assign specific user
php artisan medinext:access assign --user-email=admin@example.com --clinic-id=1 --role=admin

# List all available users, clinics, and roles
php artisan medinext:access list

# Show access status
php artisan medinext:access status
```

**What it does:**
- **Setup**: Assigns all users to default clinic with admin role
- **Assign**: Creates specific user-clinic-role relationship
- **List**: Shows all available users, clinics, roles, and current assignments
- **Status**: Shows access statistics and identifies users without access

## Usage Sequences

### Initial System Setup
```bash
# 1. Fresh setup with demo data
php artisan medinext:setup --fresh --demo

# 2. Verify permissions
php artisan medinext:permissions validate

# 3. Check access status
php artisan medinext:access status
```

### Daily Operations
```bash
# Check system status
php artisan medinext:data status
php artisan medinext:permissions status
php artisan medinext:access status

# Generate license keys for new clients
php artisan medinext:license generate --count=5 --output=new_licenses.txt
```

### Maintenance
```bash
# Reset demo data
php artisan medinext:data reset --force

# Update permissions after system changes
php artisan medinext:permissions update --force

# Validate license keys
php artisan medinext:license validate
```

### Troubleshooting
```bash
# Check for permission issues
php artisan medinext:permissions validate --fix

# Reset user access
php artisan medinext:access setup --force

# Clear and reseed data
php artisan medinext:data reset --force
```

## Command Benefits

### 1. **Unified Naming**
- All commands use `medinext:` prefix
- Consistent action-based structure
- Easy to remember and discover

### 2. **Comprehensive Functionality**
- Each command handles multiple related actions
- Reduces command clutter
- Provides complete system management

### 3. **Safety Features**
- Confirmation prompts for destructive operations
- `--force` option for automated scripts
- Dry-run capabilities for testing

### 4. **Rich Output**
- Detailed progress information
- Status summaries and statistics
- Clear success/error messages

### 5. **Flexibility**
- Multiple options for customization
- Support for specific targeting (clinic, role, user)
- File output capabilities

## Migration from Old Commands

| Old Command | New Command | Notes |
|-------------|-------------|-------|
| `demo:clear` | `medinext:data clear` | Enhanced with clinic targeting |
| `demo:create` | `medinext:setup` | Complete system setup |
| `seeders:run-optimized` | `medinext:setup` | Integrated into setup |
| `license:generate-keys` | `medinext:license generate` | Enhanced with validation |
| `setup:user-clinic-access` | `medinext:access` | Multiple access actions |
| `admin:update-permissions` | `medinext:permissions update` | Unified permissions management |
| `roles:update-permissions` | `medinext:permissions update` | Consolidated functionality |
| `permissions:validate` | `medinext:permissions validate` | Enhanced validation |

## Best Practices

1. **Always use `--force` in production scripts** to avoid interactive prompts
2. **Run `medinext:permissions validate`** after system updates
3. **Use `medinext:data status`** to monitor system health
4. **Generate license keys with `--output`** for record keeping
5. **Use `medinext:access status`** to identify access issues

## Error Handling

All commands provide:
- Clear error messages with context
- Proper exit codes (0 for success, 1 for failure)
- Detailed logging for troubleshooting
- Rollback capabilities where applicable

## Support

For issues with console commands:
1. Check command syntax and options
2. Verify database connectivity
3. Run `medinext:permissions validate` to check system integrity
4. Use `medinext:data status` to verify data consistency
