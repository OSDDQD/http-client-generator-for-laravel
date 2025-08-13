# Enhanced HTTP Client Generator for Laravel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Enhanced version of the HTTP Client Generator for Laravel with **custom namespace and path support**. This package generates classes for Laravel HTTP client with full customization capabilities.

## Features

✅ **Custom Namespace Support** - Define your own namespace structure  
✅ **Custom Path Configuration** - Specify where files should be generated  
✅ **Environment Variable Support** - Configure via .env file  
✅ **Command Line Options** - Override settings per command  
✅ **Backward Compatibility** - Works with existing projects  
✅ **Laravel 10, 11, 12 Support** - Compatible with modern Laravel versions

## Installation

### Via VCS Repository (Recommended)

Add the following to your `composer.json`:

```json
{
    "require": {
        "osddqd/http-client-generator-for-laravel": "^1.0"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/OSDDQD/http-client-generator-for-laravel.git"
        }
    ]
}
```

Then run:

```bash
composer install
```

### Publish Configuration

```bash
php artisan http-client-generator:install
```

This will publish the configuration file to `config/http-client-generator.php`.

## Configuration

### Configuration File

```php
// config/http-client-generator.php
<?php

return [
    'namespace' => [
        'base' => 'App\\Http\\Clients',        // Base namespace
        'attributes' => 'Attributes',          // Attributes subfolder
        'requests' => 'Requests',              // Requests subfolder  
        'responses' => 'Responses',            // Responses subfolder
    ],
    
    'paths' => [
        'base' => 'app/Http/Clients',                    // Base path for classes
        'tests' => 'tests/Unit/Http/Clients',           // Base path for tests
    ],
    
    'stubs' => [
        'custom_path' => null,  // Path to custom stub files (optional)
    ],
];
```

### Environment Variables

```env
# .env
HTTP_CLIENT_GENERATOR_NAMESPACE=App\\External\\Clients
HTTP_CLIENT_GENERATOR_PATH=app/External/Clients
HTTP_CLIENT_GENERATOR_TESTS_PATH=tests/Unit/External/Clients
HTTP_CLIENT_GENERATOR_STUBS_PATH=/path/to/custom/stubs
```

## Usage

### Basic Usage (Default Settings)

```bash
# Generate attribute class
php artisan http-client-generator:attribute Twitter Fetch

# Generate request class  
php artisan http-client-generator:request Twitter Fetch

# Generate response class
php artisan http-client-generator:response Twitter Fetch

# Generate bad response class
php artisan http-client-generator:bad-response Twitter
```

### With Custom Options

```bash
# Override namespace and paths
php artisan http-client-generator:attribute Twitter Fetch \
    --namespace="App\\External\\Clients" \
    --path="app/External/Clients" \
    --tests-path="tests/Unit/External/Clients"
```

### Example Output Structure

With default configuration:
```
app/Http/Clients/Twitter/
├── Attributes/
│   └── FetchAttribute.php
├── Requests/
│   └── FetchRequest.php
└── Responses/
    └── FetchResponse.php

tests/Unit/Http/Clients/Twitter/
├── Attributes/
│   └── FetchAttributeTest.php
├── Requests/
│   └── FetchRequestTest.php
└── Responses/
    └── FetchResponseTest.php
```

With custom configuration:
```
app/External/Clients/Twitter/
├── Attributes/
│   └── FetchAttribute.php
├── Requests/
│   └── FetchRequest.php
└── Responses/
    └── FetchResponse.php
```

## Generated Classes Examples

### Attribute Class
```php
<?php

namespace App\Http\Clients\Twitter\Attributes;

class FetchAttribute
{
    public function __construct(
        protected string $userId,
        protected ?int $limit = null,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'limit' => $this->limit,
        ];
    }
}
```

### Request Class
```php
<?php

namespace App\Http\Clients\Twitter\Requests;

use App\Http\Clients\Twitter\Attributes\FetchAttribute;
use App\Http\Clients\Twitter\Responses\BadResponse;
use App\Http\Clients\Twitter\Responses\FetchResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

class FetchRequest
{
    public function __construct(public Factory $client) {}

    public function send(FetchAttribute $attribute): BadResponse|FetchResponse
    {
        $response = $this->client->get('users', $attribute->toArray());

        if ($response->status() === Response::HTTP_OK) {
            return FetchResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
```

## Available Commands

| Command | Description | Options |
|---------|-------------|---------|
| `http-client-generator:install` | Install configuration file | - |
| `http-client-generator:attribute` | Generate attribute class | `--namespace`, `--path`, `--tests-path` |
| `http-client-generator:request` | Generate request class | `--namespace`, `--path`, `--tests-path` |
| `http-client-generator:response` | Generate response class | `--namespace`, `--path`, `--tests-path` |
| `http-client-generator:bad-response` | Generate bad response class | `--namespace`, `--path`, `--tests-path` |
| `http-client-generator:has-status-trait` | Generate HasStatus trait | `--namespace`, `--path` |
| `http-client-generator:client-macro` | Generate client macro | `--namespace`, `--path` |
| `http-client-generator:all` | Generate all classes | `--namespace`, `--path`, `--tests-path` |

## Migration from Original Package

This package is fully backward compatible. To migrate:

1. Update your `composer.json` to use this package
2. Run `composer update`
3. Optionally run `php artisan http-client-generator:install` to get new configuration options

All existing commands work exactly the same way.

## Advanced Usage

### Custom Stub Files

You can create your own stub templates:

1. Set `HTTP_CLIENT_GENERATOR_STUBS_PATH` in your `.env`
2. Create custom `.stub` files in that directory
3. Use the same placeholder syntax: `{{ namespace }}`, `{{ client }}`, `{{ name }}`

### Integration with External APIs

Perfect for creating organized HTTP clients for external services:

```bash
# Rapira Exchange API
php artisan http-client-generator:attribute Rapira CreateWallet \
    --namespace="App\\External\\Rapira" \
    --path="app/External/Rapira"

# CoinGecko API  
php artisan http-client-generator:request CoinGecko GetPrice \
    --namespace="App\\External\\CoinGecko" \
    --path="app/External/CoinGecko"
```

## Requirements

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0

## Credits

- **OSDDQD** - Enhanced version with custom namespace/path support
- **Janez Cergolj** - Original package author

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Changelog

### v1.0.0
- ✅ Added custom namespace support
- ✅ Added custom path configuration  
- ✅ Added environment variable support
- ✅ Added command line options
- ✅ Added configuration file
- ✅ Added install command
- ✅ Maintained backward compatibility
