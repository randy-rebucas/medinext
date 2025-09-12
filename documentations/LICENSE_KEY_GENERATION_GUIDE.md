# License Key Generation System Guide

## Overview

The MediNext application now includes a comprehensive license key generation system that provides multiple strategies for creating unique, secure license keys. This system is designed to be flexible, scalable, and easy to use across different interfaces.

## Features

- **Multiple Generation Strategies**: Standard, Compact, Segmented, and Custom formats
- **Uniqueness Guarantee**: Automatic collision detection and retry logic
- **Validation & Parsing**: Built-in format validation and key parsing
- **Multiple Interfaces**: Nova Admin, API endpoints, and Console commands
- **Audit Trail**: Complete logging of all generation activities
- **Statistics & Monitoring**: Real-time statistics and usage tracking

## Generation Strategies

### 1. Standard Strategy
**Format**: `MEDI-XXXX-XXXX-XXXX-XXXX`
**Example**: `MEDI-A1B2-C3D4-E5F6-G7H8`

```php
$key = LicenseKeyGenerator::generate(LicenseKeyGenerator::STRATEGY_STANDARD, [
    'prefix' => 'MEDI',
    'segment_length' => 4,
    'segments' => 4
]);
```

### 2. Compact Strategy
**Format**: `MEDI-XXXXXXXXXXXX`
**Example**: `MEDI-A1B2C3D4E5F6`

```php
$key = LicenseKeyGenerator::generate(LicenseKeyGenerator::STRATEGY_COMPACT, [
    'prefix' => 'MEDI',
    'length' => 12
]);
```

### 3. Segmented Strategy
**Format**: Custom segmented format
**Example**: `MEDI-XXXX-XXXX-XXXX-XXXX`

```php
$key = LicenseKeyGenerator::generate(LicenseKeyGenerator::STRATEGY_SEGMENTED, [
    'format' => 'MEDI-{segment1}-{segment2}-{segment3}-{segment4}',
    'segment_length' => 4
]);
```

### 4. Custom Strategy
**Format**: User-defined with placeholders
**Example**: `CUSTOM-{random:4}-{year}` → `CUSTOM-A1B2-2024`

```php
$key = LicenseKeyGenerator::generate(LicenseKeyGenerator::STRATEGY_CUSTOM, [
    'format' => 'CUSTOM-{random:4}-{year}-{month}'
]);
```

**Available Placeholders**:
- `{random:N}` - N random alphanumeric characters
- `{timestamp:format}` - Current timestamp in specified format
- `{year}` - Current year (YYYY)
- `{month}` - Current month (MM)
- `{day}` - Current day (DD)

## Usage Examples

### 1. Nova Admin Interface

1. Navigate to **System Management** → **Licenses**
2. Select a license or use bulk actions
3. Click **Actions** → **Generate License Key**
4. Choose strategy and configure options
5. Click **Run Action**

### 2. API Endpoints

#### Generate Single Key
```bash
curl -X POST http://localhost:8000/api/v1/license/key/generate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "strategy": "standard",
    "options": {
      "prefix": "MEDI",
      "segment_length": 4,
      "segments": 4
    }
  }'
```

#### Generate Multiple Keys
```bash
curl -X POST http://localhost:8000/api/v1/license/key/generate-multiple \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "count": 10,
    "strategy": "compact",
    "options": {
      "prefix": "MEDI",
      "length": 12
    }
  }'
```

#### Validate Key Format
```bash
curl -X POST http://localhost:8000/api/v1/license/key/validate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "license_key": "MEDI-A1B2-C3D4-E5F6-G7H8",
    "strategy": "standard"
  }'
```

#### Get Statistics
```bash
curl -X GET http://localhost:8000/api/v1/license/key/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Console Commands

#### Generate Single Key
```bash
php artisan license:generate-keys --count=1 --strategy=standard
```

#### Generate Multiple Keys
```bash
php artisan license:generate-keys --count=100 --strategy=compact --output=keys.txt
```

#### Custom Format
```bash
php artisan license:generate-keys --count=5 --strategy=custom --format="CUSTOM-{random:4}-{year}"
```

#### Dry Run (Preview)
```bash
php artisan license:generate-keys --count=10 --strategy=standard --dry-run
```

#### With Validation
```bash
php artisan license:generate-keys --count=50 --strategy=standard --validate
```

### 4. Programmatic Usage

#### Basic Generation
```php
use App\Services\LicenseKeyGenerator;

// Generate standard key
$key = LicenseKeyGenerator::generate();

// Generate with specific strategy
$key = LicenseKeyGenerator::generate(LicenseKeyGenerator::STRATEGY_COMPACT);

// Generate with custom options
$key = LicenseKeyGenerator::generate(LicenseKeyGenerator::STRATEGY_STANDARD, [
    'prefix' => 'PRM',
    'segment_length' => 6,
    'segments' => 3
]);
```

#### Multiple Key Generation
```php
// Generate 10 keys
$keys = LicenseKeyGenerator::generateMultiple(10, LicenseKeyGenerator::STRATEGY_STANDARD);

// Generate with characteristics
$key = LicenseKeyGenerator::generateWithCharacteristics([
    'license_type' => 'premium',
    'strategy' => LicenseKeyGenerator::STRATEGY_STANDARD
]);
```

#### Validation and Parsing
```php
// Validate format
$isValid = LicenseKeyGenerator::validateFormat('MEDI-A1B2-C3D4-E5F6-G7H8');

// Parse key information
$info = LicenseKeyGenerator::parseLicenseKey('MEDI-A1B2-C3D4-E5F6-G7H8');
// Returns: ['prefix' => 'MEDI', 'segments' => ['A1B2', 'C3D4', 'E5F6', 'G7H8'], ...]

// Check if key exists
$exists = LicenseKeyGenerator::keyExists('MEDI-A1B2-C3D4-E5F6-G7H8');
```

#### License Model Integration
```php
use App\Models\License;

// Create license (auto-generates key)
$license = License::create([
    'name' => 'Test License',
    'license_type' => 'premium',
    // license_key will be auto-generated
]);

// Regenerate existing license key
$newKey = $license->regenerateLicenseKey(LicenseKeyGenerator::STRATEGY_COMPACT);

// Validate license key format
$isValid = $license->validateLicenseKeyFormat();

// Get key information
$keyInfo = $license->getLicenseKeyInfo();
```

## API Reference

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/license/key/generate` | Generate single license key |
| POST | `/api/v1/license/key/generate-multiple` | Generate multiple license keys |
| POST | `/api/v1/license/key/validate` | Validate license key format |
| POST | `/api/v1/license/key/parse` | Parse license key information |
| GET | `/api/v1/license/key/statistics` | Get generation statistics |
| GET | `/api/v1/license/key/strategies` | Get available strategies |
| POST | `/api/v1/license/key/regenerate/{license}` | Regenerate key for existing license |

### Request/Response Examples

#### Generate Single Key Response
```json
{
  "success": true,
  "message": "License key generated successfully",
  "data": {
    "license_key": "MEDI-A1B2-C3D4-E5F6-G7H8",
    "strategy": "standard",
    "options": {
      "prefix": "MEDI",
      "segment_length": 4,
      "segments": 4
    },
    "format_info": {
      "prefix": "MEDI",
      "segments": ["A1B2", "C3D4", "E5F6", "G7H8"],
      "segment_count": 4,
      "total_length": 23,
      "format": "standard"
    }
  }
}
```

#### Statistics Response
```json
{
  "success": true,
  "data": {
    "total_licenses": 150,
    "by_type": {
      "standard": 50,
      "premium": 75,
      "enterprise": 25
    },
    "by_status": {
      "active": 120,
      "expired": 20,
      "suspended": 10
    },
    "expiring_soon": 15,
    "generation_strategies": {
      "standard": "Standard (MEDI-XXXX-XXXX-XXXX-XXXX)",
      "compact": "Compact (MEDI-XXXXXXXXXXXX)",
      "segmented": "Segmented (Custom segments)",
      "custom": "Custom (User-defined format)"
    }
  }
}
```

## Console Command Options

```bash
php artisan license:generate-keys [options]

Options:
  --count=COUNT              Number of license keys to generate (default: 1)
  --strategy=STRATEGY        Generation strategy (default: standard)
  --prefix=PREFIX           License key prefix (default: MEDI)
  --segment-length=LENGTH   Length of each segment (default: 4)
  --segments=SEGMENTS       Number of segments (default: 4)
  --length=LENGTH           Total length for compact strategy (default: 12)
  --format=FORMAT           Custom format for segmented/custom strategies
  --output=FILE             Output file path to save generated keys
  --dry-run                 Show what would be generated without actually generating
  --validate                Validate generated keys
```

## Security Considerations

1. **Uniqueness**: All generated keys are guaranteed to be unique through database collision detection
2. **Audit Trail**: All generation activities are logged with user information and timestamps
3. **Access Control**: API endpoints require admin permissions
4. **Rate Limiting**: Consider implementing rate limiting for bulk generation
5. **Key Storage**: License keys are stored securely in the database with proper indexing

## Performance Considerations

1. **Caching**: Key existence checks are cached for 1 hour to improve performance
2. **Batch Generation**: Use `generateMultiple()` for bulk operations instead of individual calls
3. **Database Indexing**: Ensure proper indexing on the `license_key` column
4. **Memory Usage**: For very large batches, consider processing in chunks

## Error Handling

The system includes comprehensive error handling:

- **Validation Errors**: Invalid parameters return detailed error messages
- **Collision Detection**: Automatic retry with exponential backoff
- **Rate Limiting**: Graceful handling of generation limits
- **Logging**: All errors are logged with context information

## Monitoring and Statistics

Access real-time statistics through:

1. **API Endpoint**: `GET /api/v1/license/key/statistics`
2. **Nova Dashboard**: View metrics in the admin interface
3. **Console Command**: Use `--validate` flag for immediate feedback
4. **Application Logs**: Monitor generation activities and errors

## Best Practices

1. **Use Appropriate Strategy**: Choose the right strategy for your use case
2. **Batch Operations**: Generate multiple keys in single operations when possible
3. **Validate Keys**: Always validate generated keys before use
4. **Monitor Usage**: Keep track of generation statistics and patterns
5. **Secure Storage**: Store generated keys securely and limit access
6. **Regular Cleanup**: Remove unused or expired keys from the system

## Troubleshooting

### Common Issues

1. **"Unable to generate unique license key"**
   - Solution: Reduce batch size or check for database constraints

2. **"Invalid strategy"**
   - Solution: Use one of the predefined strategies or check spelling

3. **"Custom format is required"**
   - Solution: Provide a format string when using custom strategy

4. **"Validation failed"**
   - Solution: Check parameter types and ranges

### Debug Mode

Enable debug logging by setting `LOG_LEVEL=debug` in your environment configuration.

## Future Enhancements

Potential future improvements:

1. **Encryption**: Add encryption/decryption capabilities
2. **Expiration**: Built-in key expiration handling
3. **Revocation**: Key revocation and blacklisting
4. **Analytics**: Advanced usage analytics and reporting
5. **Integration**: Third-party service integrations
6. **Templates**: Predefined key templates for common use cases

## Support

For issues or questions regarding the license key generation system:

1. Check the application logs for detailed error information
2. Review the API documentation for proper usage
3. Test with the console command for debugging
4. Contact the development team for advanced support
