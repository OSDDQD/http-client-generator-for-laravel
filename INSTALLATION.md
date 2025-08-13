# Installation Guide

## Method 1: VCS Repository (Recommended)

### Step 1: Add to composer.json

Add the following to your Laravel project's `composer.json`:

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

### Step 2: Install the package

```bash
composer install
# or if updating existing project
composer update osddqd/http-client-generator-for-laravel
```

### Step 3: Publish configuration

```bash
php artisan http-client-generator:install
```

## Method 2: Direct Git Clone (Development)

For development or testing purposes:

```bash
# Clone the repository
git clone https://github.com/OSDDQD/http-client-generator-for-laravel.git

# Add to your Laravel project's composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "./path/to/http-client-generator-for-laravel"
        }
    ],
    "require": {
        "osddqd/http-client-generator-for-laravel": "*"
    }
}
```

## Configuration Examples

### Example 1: Default Laravel Structure

```php
// config/http-client-generator.php
return [
    'namespace' => [
        'base' => 'App\\Http\\Clients',
    ],
    'paths' => [
        'base' => 'app/Http/Clients',
        'tests' => 'tests/Unit/Http/Clients',
    ],
];
```

**Generated structure:**
```
app/Http/Clients/Twitter/
├── Attributes/FetchAttribute.php
├── Requests/FetchRequest.php
└── Responses/FetchResponse.php
```

### Example 2: External Services Structure

```php
// config/http-client-generator.php
return [
    'namespace' => [
        'base' => 'App\\External\\Clients',
    ],
    'paths' => [
        'base' => 'app/External/Clients',
        'tests' => 'tests/Unit/External/Clients',
    ],
];
```

**Generated structure:**
```
app/External/Clients/Rapira/
├── Attributes/CreateWalletAttribute.php
├── Requests/CreateWalletRequest.php
└── Responses/CreateWalletResponse.php
```

### Example 3: Microservice Architecture

```php
// config/http-client-generator.php
return [
    'namespace' => [
        'base' => 'App\\Services\\HttpClients',
    ],
    'paths' => [
        'base' => 'app/Services/HttpClients',
        'tests' => 'tests/Unit/Services/HttpClients',
    ],
];
```

### Example 4: Environment-based Configuration

```env
# .env
HTTP_CLIENT_GENERATOR_NAMESPACE=App\\Integration\\Clients
HTTP_CLIENT_GENERATOR_PATH=app/Integration/Clients
HTTP_CLIENT_GENERATOR_TESTS_PATH=tests/Integration/Clients
```

## Usage Examples

### Basic Usage

```bash
# Generate Twitter API client
php artisan http-client-generator:attribute Twitter GetTweets
php artisan http-client-generator:request Twitter GetTweets  
php artisan http-client-generator:response Twitter GetTweets

# Generate Rapira Exchange client
php artisan http-client-generator:attribute Rapira CreateWallet
php artisan http-client-generator:request Rapira CreateWallet
php artisan http-client-generator:response Rapira CreateWallet
```

### With Custom Paths

```bash
# Generate for external API with custom namespace
php artisan http-client-generator:attribute CoinGecko GetPrice \
    --namespace="App\\External\\CoinGecko" \
    --path="app/External/CoinGecko" \
    --tests-path="tests/Unit/External/CoinGecko"
```

### For CryptoPay Project

Perfect for your crypto processing backend:

```bash
# Rapira Exchange integration
php artisan http-client-generator:attribute Rapira CreateWallet \
    --namespace="App\\External\\Rapira" \
    --path="app/External/Rapira"

php artisan http-client-generator:request Rapira CreateWallet \
    --namespace="App\\External\\Rapira" \
    --path="app/External/Rapira"

php artisan http-client-generator:response Rapira CreateWallet \
    --namespace="App\\External\\Rapira" \
    --path="app/External/Rapira"

# Gmail API integration  
php artisan http-client-generator:attribute Gmail SendEmail \
    --namespace="App\\External\\Gmail" \
    --path="app/External/Gmail"
```

## Troubleshooting

### Issue: Package not found

**Solution:** Make sure the repository is added to `composer.json` before the require section.

### Issue: Classes generated in wrong location

**Solution:** Check your configuration file or use command-line options to override paths.

### Issue: Namespace conflicts

**Solution:** Use custom namespaces to avoid conflicts with existing code.

### Issue: Permission denied

**Solution:** Make sure your Laravel project has write permissions for the target directories.

## Verification

After installation, verify the package works:

```bash
# Check available commands
php artisan list http-client-generator

# Test generation
php artisan http-client-generator:attribute Test Example
```

You should see the generated files in your configured paths.
