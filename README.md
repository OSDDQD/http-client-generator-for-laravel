# Enhanced HTTP Client Generator for Laravel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Enhanced version of the HTTP Client Generator for Laravel with **custom namespace and path support**. This package generates classes for Laravel HTTP client with full customization capabilities.

## Features

✅ **Custom Namespace Support** - Define your own namespace structure
✅ **Custom Path Configuration** - Specify where files should be generated
✅ **Optional Test Generation** - Control test creation with `--no-tests` option
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

Для публикации файла конфигурации используйте стандартную команду Laravel:

```bash
php artisan vendor:publish --provider="Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider" --tag="config"
```

Это опубликует файл конфигурации в `config/http-client-generator.php`.

Вы также можете опубликовать stub-файлы для кастомизации шаблонов:

```bash
php artisan vendor:publish --provider="Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider" --tag="stubs"
```

## Configuration

Пакет использует стандартный механизм Laravel для конфигурации. Конфигурация автоматически объединяется с настройками приложения, что позволяет переопределять только необходимые параметры.

### Default Package Configuration

Пакет предоставляет конфигурацию по умолчанию, которая автоматически загружается через `mergeConfigFrom()`. Это означает, что вы можете использовать пакет без публикации конфигурации, а опубликованный файл конфигурации будет содержать только те параметры, которые вы хотите переопределить.

### Configuration File

После публикации конфигурации (`vendor:publish --tag="config"`) вы получите файл:

```php
// config/http-client-generator.php
<?php

return [
    'namespace' => [
        'base' => 'App\\Http\\Clients',        // Base namespace
        'attributes' => 'Attributes',          // Attributes subfolder
        'requests' => 'Requests',              // Requests subfolder
        'responses' => 'Responses',            // Responses subfolder
        'factories' => 'Factories',            // Factories subfolder
    ],

    'paths' => [
        'base' => 'app/Http/Clients',                    // Base path for classes
        'tests' => 'tests/Unit/Http/Clients',           // Base path for tests
    ],

    'stubs' => [
        'custom_path' => null,  // Path to custom stub files (optional)
    ],

    // Test generation settings
    'generate_tests' => true,  // Generate tests by default (can be overridden with --no-tests)
];
```

**Важно:** Вы можете удалить из опубликованного файла конфигурации любые параметры, которые вас устраивают по умолчанию. Пакет автоматически объединит вашу конфигурацию с настройками по умолчанию.

### Partial Configuration Example

Например, если вы хотите изменить только базовый namespace, ваш опубликованный файл конфигурации может содержать только:

```php
// config/http-client-generator.php
<?php

return [
    'namespace' => [
        'base' => 'App\\External\\Clients',
    ],
];
```

Все остальные настройки будут автоматически взяты из конфигурации пакета по умолчанию.

### Environment Variables

```env
# .env
HTTP_CLIENT_GENERATOR_NAMESPACE=App\\External\\Clients
HTTP_CLIENT_GENERATOR_PATH=app/External/Clients
HTTP_CLIENT_GENERATOR_TESTS_PATH=tests/Unit/External/Clients
HTTP_CLIENT_GENERATOR_STUBS_PATH=/path/to/custom/stubs

# Test generation settings
HTTP_CLIENT_GENERATOR_GENERATE_TESTS=true
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

# Generate factory class
php artisan http-client-generator:factory Twitter Api

# Generate all classes at once
php artisan http-client-generator:all Twitter Fetch
```

### Test Generation Control

By default, the package generates both classes and their corresponding test files. You can control this behavior:

```bash
# Skip test generation for a single command
php artisan http-client-generator:attribute Twitter Fetch --no-tests

# Skip test generation for all classes
php artisan http-client-generator:all Twitter Fetch --no-tests

# Disable test generation globally in config
# Set 'generate_tests' => false in config/http-client-generator.php
# or HTTP_CLIENT_GENERATOR_GENERATE_TESTS=false in .env
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
├── Responses/
│   └── FetchResponse.php
└── Factories/
    └── FetchFactory.php

tests/Unit/Http/Clients/Twitter/
├── Attributes/
│   └── FetchAttributeTest.php
├── Requests/
│   └── FetchRequestTest.php
├── Responses/
│   └── FetchResponseTest.php
└── Factories/
    └── FetchFactoryTest.php
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
| `http-client-generator:attribute` | Generate attribute class | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:request` | Generate request class | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:response` | Generate response class | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:bad-response` | Generate bad response class | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:factory` | Generate HTTP client factory class | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:all` | Generate all classes | `--namespace`, `--path`, `--tests-path`, `--no-tests` |

## Migration from Original Package

This package is fully backward compatible. To migrate:

1. Update your `composer.json` to use this package
2. Run `composer update`
3. Optionally publish configuration file using `php artisan vendor:publish --provider="Osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider" --tag="config"`

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
# CoinGecko API  
php artisan http-client-generator:request CoinGecko GetPrice \
    --namespace="App\\External\\CoinGecko" \
    --path="app/External/CoinGecko"
```


## Requirements

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0

## Credits

- **Janez Cergolj** - Original package author

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
