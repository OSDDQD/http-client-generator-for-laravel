# HTTP Client Generator для Laravel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Мощный генератор HTTP клиентов для Laravel с поддержкой кастомных namespace и путей. Этот пакет автоматизирует создание структурированных классов для работы с внешними API, включая автоматическую генерацию тестов.

## 🚀 Описание проекта

HTTP Client Generator - это инструмент для Laravel, который автоматизирует создание классов для работы с внешними HTTP API. Пакет генерирует полную структуру классов:

- **Attribute классы** - для подготовки данных запроса
- **Request классы** - для выполнения HTTP запросов
- **Response классы** - для обработки успешных ответов
- **BadResponse классы** - для обработки ошибок
- **Factory классы** - для создания HTTP клиентов
- **Test классы** - автоматические PHPUnit тесты для всех компонентов

## ✨ Основные возможности

- **🎯 Кастомные namespace**: Полная настройка структуры namespace под ваш проект
- **📁 Гибкие пути**: Определение собственных путей для генерируемых файлов
- **🧪 Автоматические тесты**: Генерация PHPUnit тестов для всех классов
- **⚙️ Гибкая конфигурация**: Настройка через переменные окружения и конфиг файлы
- **🔄 Обратная совместимость**: Работает с существующими паттернами Laravel HTTP client
- **📦 Пакетная генерация**: Создание всех классов одной командой или по отдельности
- **🎨 Кастомные шаблоны**: Возможность переопределения stub файлов

## 📦 Установка и настройка

### Установка через Composer

Добавьте пакет в ваш Laravel проект:

```json
{
    "require": {
        "osddqd/http-client-generator-for-laravel": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/osddqd/http-client-generator-for-laravel.git"
        }
    ]
}
```

Затем выполните:

```bash
composer install
```

### Публикация конфигурации

Для публикации файла конфигурации:

```bash
php artisan vendor:publish --provider="osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider" --tag="config"
```

Для публикации stub-файлов (шаблонов):

```bash
php artisan vendor:publish --provider="osddqd\HttpClientGenerator\HttpClientGeneratorServiceProvider" --tag="stubs"
```

### Настройка переменных окружения

Добавьте в ваш `.env` файл:

```env
# Базовый namespace для HTTP клиентов
HTTP_CLIENT_GENERATOR_NAMESPACE=App\\Http\\Clients

# Путь для генерируемых классов
HTTP_CLIENT_GENERATOR_PATH=app/Http/Clients

# Путь для тестов
HTTP_CLIENT_GENERATOR_TESTS_PATH=tests/Unit/Http/Clients

# Автоматическая генерация тестов (по умолчанию true)
HTTP_CLIENT_GENERATOR_GENERATE_TESTS=true

# Кастомный путь к stub файлам (опционально)
HTTP_CLIENT_GENERATOR_STUBS_PATH=/path/to/custom/stubs
```

## 🚀 Быстрый старт

### Создание всех классов одной командой

```bash
# Создание полного набора классов для работы с GitHub API
php artisan http-client-generator:all GitHub GetUser
```

Эта команда создаст:
- `App\Http\Clients\GitHub\Attributes\GetUserAttribute`
- `App\Http\Clients\GitHub\Requests\GetUserRequest`
- `App\Http\Clients\GitHub\Responses\GetUserResponse`
- `App\Http\Clients\GitHub\Responses\BadResponse`
- `App\Http\Clients\GitHub\Factories\ApiFactory`
- Соответствующие тесты в `tests/Unit/Http/Clients/GitHub/`

### Создание отдельных классов

```bash
# Создание только класса атрибутов
php artisan http-client-generator:attribute GitHub GetUser

# Создание только класса запроса
php artisan http-client-generator:request GitHub GetUser

# Создание только класса ответа
php artisan http-client-generator:response GitHub GetUser

# Создание класса для обработки ошибок
php artisan http-client-generator:bad-response GitHub

# Создание фабрики
php artisan http-client-generator:factory GitHub Api
```

### Пример использования сгенерированных классов

```php
<?php

use App\Http\Clients\GitHub\Attributes\GetUserAttribute;
use App\Http\Clients\GitHub\Requests\GetUserRequest;
use Illuminate\Http\Client\Factory;

// Создание атрибутов запроса
$attribute = new GetUserAttribute(
    username: 'octocat'
);

// Выполнение запроса
$httpClient = app(Factory::class);
$request = new GetUserRequest($httpClient);
$response = $request->send($attribute);

// Обработка ответа
if ($response->success()) {
    // Успешный ответ
    $userData = $response->original->json();
} else {
    // Обработка ошибки
    $errorMessage = $response->original->body();
}
```

## 🏗️ Структура проекта

После генерации классов ваш проект будет иметь следующую структуру:

```
app/Http/Clients/
├── GitHub/
│   ├── Attributes/
│   │   └── GetUserAttribute.php
│   ├── Requests/
│   │   └── GetUserRequest.php
│   ├── Responses/
│   │   ├── GetUserResponse.php
│   │   └── BadResponse.php
│   └── Factories/
│       └── ApiFactory.php
└── ...

tests/Unit/Http/Clients/
├── GitHub/
│   ├── Attributes/
│   │   └── GetUserAttributeTest.php
│   ├── Requests/
│   │   └── GetUserRequestTest.php
│   ├── Responses/
│   │   ├── GetUserResponseTest.php
│   │   └── BadResponseTest.php
│   └── Factories/
│       └── ApiFactoryTest.php
└── ...
```

## 🔧 Доступные команды

### Команды генерации классов

| Команда | Описание | Опции |
|---------|----------|-------|
| `http-client-generator:all` | Создать все классы сразу | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:attribute` | Создать класс атрибутов | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:request` | Создать класс запроса | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:response` | Создать класс ответа | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:bad-response` | Создать класс ошибки | `--namespace`, `--path`, `--tests-path`, `--no-tests` |
| `http-client-generator:factory` | Создать фабрику | `--namespace`, `--path`, `--tests-path`, `--no-tests` |

### Команды генерации тестов

| Команда | Описание | Опции |
|---------|----------|-------|
| `http-client-generator:test:all` | Создать все тесты | `--test-namespace` |
| `http-client-generator:test:attribute` | Создать тест атрибутов | `--test-namespace` |
| `http-client-generator:test:request` | Создать тест запроса | `--test-namespace` |
| `http-client-generator:test:response` | Создать тест ответа | `--test-namespace` |
| `http-client-generator:test:bad-response` | Создать тест ошибки | `--test-namespace` |
| `http-client-generator:test:factory` | Создать тест фабрики | `--test-namespace` |

## 📚 Ссылки на документацию

- **[EXAMPLES.md](EXAMPLES.md)** - Подробные примеры использования с комплексными сценариями

## 🔧 Системные требования

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0

## 📄 Лицензия

MIT License. Подробности в файле [LICENSE](LICENSE).