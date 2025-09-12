# License Auto-Fill System Guide

## Overview

The License Auto-Fill system provides a Nova action that automatically populates license fields based on the selected license type (Standard, Premium, or Enterprise). This ensures consistency across licenses and saves time when creating or updating multiple licenses.

## Features

- **Automatic Field Population**: Fills all relevant fields based on license type
- **Feature Mapping**: Automatically sets appropriate features for each license type
- **Flexible Options**: Choose to overwrite existing fields or only fill empty ones
- **Custom Duration**: Override default validity periods
- **Custom Grace Period**: Override default grace periods
- **Bulk Operations**: Apply to multiple licenses at once
- **Audit Trail**: Complete logging of all auto-fill operations

## License Type Configurations

### Standard License
**Target**: Small practices and individual practitioners
**Monthly Fee**: $99.00
**Billing Cycle**: Monthly

**Features**:
- Basic appointments
- Patient management
- Prescription management
- Basic reporting

**Limits**:
- Max Users: 5
- Max Clinics: 1
- Max Patients: 500
- Max Appointments/Month: 200

**Other Settings**:
- Grace Period: 7 days
- Support Level: Standard
- Validity: 12 months

### Premium License
**Target**: Growing practices and small clinics
**Monthly Fee**: $299.00
**Billing Cycle**: Monthly

**Features**:
- All Standard features, plus:
- Advanced reporting
- Lab results management
- Medical representative management
- Multi-clinic support
- Email notifications

**Limits**:
- Max Users: 25
- Max Clinics: 5
- Max Patients: 2,500
- Max Appointments/Month: 1,000

**Other Settings**:
- Grace Period: 14 days
- Support Level: Premium
- Validity: 12 months

### Enterprise License
**Target**: Large organizations and hospital systems
**Monthly Fee**: $999.00
**Billing Cycle**: Yearly

**Features**:
- All Premium features, plus:
- SMS notifications
- API access
- Custom branding
- Priority support
- Advanced analytics
- Backup and restore

**Limits**:
- Max Users: 100
- Max Clinics: 20
- Max Patients: 10,000
- Max Appointments/Month: 5,000

**Other Settings**:
- Grace Period: 30 days
- Support Level: Enterprise
- Validity: 12 months

## How to Use

### Via Nova Admin Interface

1. **Navigate to Licenses**
   - Go to **System Management** → **Licenses**

2. **Select License(s)**
   - Choose one or more licenses to update
   - You can select multiple licenses for bulk operations

3. **Run Auto-Fill Action**
   - Click **Actions** → **Auto Fill License Fields**
   - Select the desired license type
   - Configure options as needed
   - Click **Run Action**

### Action Options

#### License Type
- **Standard**: Basic features for small practices
- **Premium**: Advanced features for growing practices
- **Enterprise**: Full features for large organizations

#### Overwrite Existing Fields
- **Disabled (Default)**: Only fills empty fields
- **Enabled**: Overwrites all existing field values

#### Custom Duration (Months)
- **Optional**: Override the default 12-month validity period
- **Range**: 1-120 months
- **Default**: Uses license type default (12 months)

#### Custom Grace Period (Days)
- **Optional**: Override the default grace period
- **Range**: 0-365 days
- **Default**: Uses license type default

## Programmatic Usage

### Using the Model Method

```php
use App\Models\License;

// Create a new license instance
$license = new License();

// Auto-fill with Standard type (only empty fields)
$updateData = $license->autoFillFromType('standard', false);

// Auto-fill with Premium type (overwrite existing)
$updateData = $license->autoFillFromType('premium', true);

// Apply the updates
$license->update($updateData);
```

### Getting License Type Configurations

```php
use App\Models\License;

// Get configuration for specific type
$config = License::getLicenseTypeConfig('premium');

// Get all configurations
$allConfigs = License::getAllLicenseTypeConfigs();

// Access specific information
$features = $config['features'];
$limits = $config['limits'];
$billing = $config['billing'];
```

## Fields That Get Auto-Filled

### Core License Information
- `license_type` - The selected license type
- `description` - License type description
- `status` - Set to 'active'

### Features
- `features` - Array of enabled features based on license type

### Usage Limits
- `max_users` - Maximum number of users
- `max_clinics` - Maximum number of clinics
- `max_patients` - Maximum number of patients
- `max_appointments_per_month` - Monthly appointment limit

### Billing Information
- `monthly_fee` - Monthly subscription fee
- `billing_cycle` - Billing frequency (monthly/yearly)
- `auto_renew` - Auto-renewal setting

### Support & Validity
- `support_level` - Support tier (standard/premium/enterprise)
- `grace_period_days` - Grace period after expiration
- `starts_at` - License start date (current date)
- `expires_at` - License expiration date

## Example Scenarios

### Scenario 1: Creating New Licenses
1. Create a new license with minimal information
2. Run Auto-Fill action with desired license type
3. All fields are populated with appropriate defaults
4. Customize specific fields as needed

### Scenario 2: Updating Existing Licenses
1. Select existing licenses that need updates
2. Run Auto-Fill action with "Overwrite Existing Fields" enabled
3. All fields are updated to match the selected license type
4. Review and adjust any custom requirements

### Scenario 3: Bulk License Management
1. Select multiple licenses of different types
2. Run Auto-Fill action for each license type group
3. Efficiently standardize license configurations
4. Ensure consistency across your license portfolio

## Best Practices

### 1. Use Appropriate License Types
- **Standard**: For individual practitioners or very small practices
- **Premium**: For growing practices with multiple providers
- **Enterprise**: For large organizations with complex needs

### 2. Overwrite vs. Fill Empty
- **Fill Empty (Default)**: Use when you want to preserve existing customizations
- **Overwrite**: Use when you want to standardize all licenses of a type

### 3. Custom Durations
- Consider your business model when setting custom durations
- Longer durations may require different pricing strategies
- Shorter durations allow for more frequent renewals

### 4. Grace Periods
- Standard: 7 days (quick turnaround for small practices)
- Premium: 14 days (balanced approach for growing practices)
- Enterprise: 30 days (extended period for large organizations)

### 5. Bulk Operations
- Group licenses by type before running auto-fill
- Use bulk operations for efficiency
- Review results after bulk operations

## Audit Trail

All auto-fill operations are logged in the license audit trail with:
- Action performed
- License type applied
- Fields updated
- Overwrite setting used
- Timestamp
- User who performed the action

## Troubleshooting

### Common Issues

1. **No Fields Updated**
   - Check if "Overwrite Existing Fields" is enabled
   - Verify that fields are actually empty
   - Ensure the license type is valid

2. **Unexpected Field Values**
   - Review the license type configuration
   - Check if custom duration/grace period was applied
   - Verify the overwrite setting

3. **Permission Errors**
   - Ensure user has 'manage-licenses' permission
   - Check Nova action authorization settings

### Debug Information

The system provides detailed feedback:
- Number of licenses updated
- Number of licenses skipped
- Specific fields that were updated
- Any errors encountered

## API Integration

While the auto-fill functionality is primarily designed for Nova admin interface, the underlying model methods can be used in API endpoints or custom applications:

```php
// In a controller or service
public function autoFillLicense($licenseId, $licenseType, $overwrite = false)
{
    $license = License::findOrFail($licenseId);
    $updateData = $license->autoFillFromType($licenseType, $overwrite);
    
    if (!empty($updateData)) {
        $license->update($updateData);
        return response()->json(['success' => true, 'updated_fields' => array_keys($updateData)]);
    }
    
    return response()->json(['success' => false, 'message' => 'No fields to update']);
}
```

## Future Enhancements

Potential improvements for the auto-fill system:

1. **Custom Templates**: Allow users to create custom license type templates
2. **Conditional Logic**: Auto-fill based on customer size or industry
3. **Integration**: Connect with CRM systems for automatic license creation
4. **Analytics**: Track usage patterns and optimize configurations
5. **Validation**: Add validation rules for custom configurations

## Support

For issues or questions regarding the auto-fill system:

1. Check the audit trail for detailed operation logs
2. Review the license type configurations
3. Test with a single license before bulk operations
4. Contact the development team for advanced support

The auto-fill system is designed to streamline license management while maintaining flexibility for custom requirements. Use it as a starting point and customize as needed for your specific use cases.
