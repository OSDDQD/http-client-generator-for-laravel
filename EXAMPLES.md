# Примеры использования HTTP Client Generator для Laravel

Этот файл содержит подробные примеры использования сгенерированных классов HTTP клиента с комментариями на русском языке и объяснением архитектурных решений.

## 📋 Содержание

1. [Базовые примеры](#базовые-примеры)
2. [Комплексные примеры](#комплексные-примеры)
3. [Архитектурные решения](#архитектурные-решения)
4. [Рекомендации для production](#рекомендации-для-production)

## 🚀 Базовые примеры

### 1. Простой GET запрос

```php
<?php

namespace App\Http\Clients\GitHub\Attributes;

/**
 * Класс атрибутов для получения информации о пользователе GitHub
 * 
 * Архитектурное решение: Использование отдельного класса для атрибутов
 * позволяет валидировать данные на этапе создания объекта и обеспечивает
 * типобезопасность при передаче параметров в запрос.
 */
class GetUserAttribute
{
    public function __construct(
        protected string $username,
        protected bool $includePrivateRepos = false
    ) {
        // Валидация входных данных на этапе создания объекта
        if (empty($this->username)) {
            throw new \InvalidArgumentException('Username не может быть пустым');
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $this->username)) {
            throw new \InvalidArgumentException('Username содержит недопустимые символы');
        }
    }

    /**
     * Преобразование атрибутов в массив для HTTP запроса
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'include_private_repos' => $this->includePrivateRepos,
        ];
    }

    /**
     * Получение параметров для URL
     * 
     * @return array<string, string>
     */
    public function toUrlParams(): array
    {
        return [
            'username' => $this->username,
        ];
    }
}
```

```php
<?php

namespace App\Http\Clients\GitHub\Requests;

use App\Http\Clients\GitHub\Attributes\GetUserAttribute;
use App\Http\Clients\GitHub\Responses\BadResponse;
use App\Http\Clients\GitHub\Responses\GetUserResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Класс для выполнения запроса получения пользователя GitHub
 * 
 * Архитектурное решение: Инкапсуляция логики HTTP запроса в отдельном классе
 * обеспечивает единообразие обработки запросов и упрощает тестирование.
 */
class GetUserRequest
{
    public function __construct(
        public Factory $client,
        protected string $baseUrl = 'https://api.github.com'
    ) {}

    /**
     * Выполнение запроса получения пользователя
     * 
     * @param GetUserAttribute $attribute Атрибуты запроса
     * @return BadResponse|GetUserResponse
     */
    public function send(GetUserAttribute $attribute): BadResponse|GetUserResponse
    {
        try {
            // Логирование исходящего запроса для отладки
            Log::info('GitHub API: Отправка запроса получения пользователя', [
                'username' => $attribute->toUrlParams()['username'],
                'timestamp' => now()->toISOString(),
            ]);

            // Выполнение HTTP запроса с таймаутом
            $response = $this->client
                ->timeout(30) // Таймаут 30 секунд
                ->retry(3, 1000) // 3 попытки с интервалом 1 секунда
                ->withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-HTTP-Client/1.0',
                ])
                ->get("{$this->baseUrl}/users/{$attribute->toUrlParams()['username']}");

            // Логирование ответа
            Log::info('GitHub API: Получен ответ', [
                'status' => $response->status(),
                'response_time' => $response->transferStats?->getTransferTime(),
            ]);

            // Обработка успешного ответа
            if ($response->successful()) {
                return GetUserResponse::fromResponse($response);
            }

            // Обработка ошибок
            return BadResponse::fromResponse($response);

        } catch (\Exception $e) {
            // Логирование ошибок
            Log::error('GitHub API: Ошибка при выполнении запроса', [
                'error' => $e->getMessage(),
                'username' => $attribute->toUrlParams()['username'],
            ]);

            // Возврат ошибки в виде BadResponse
            throw $e;
        }
    }
}
```

### 2. POST запрос с данными

```php
<?php

namespace App\Http\Clients\GitHub\Attributes;

/**
 * Атрибуты для создания репозитория в GitHub
 */
class CreateRepositoryAttribute
{
    public function __construct(
        protected string $name,
        protected string $description = '',
        protected bool $private = false,
        protected bool $hasIssues = true,
        protected bool $hasProjects = true,
        protected bool $hasWiki = true,
        protected ?string $gitignoreTemplate = null,
        protected ?string $licenseTemplate = null
    ) {
        // Валидация обязательных полей
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Название репозитория не может быть пустым');
        }

        // Валидация формата названия репозитория
        if (!preg_match('/^[a-zA-Z0-9\-_.]+$/', $this->name)) {
            throw new \InvalidArgumentException('Название репозитория содержит недопустимые символы');
        }
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'private' => $this->private,
            'has_issues' => $this->hasIssues,
            'has_projects' => $this->hasProjects,
            'has_wiki' => $this->hasWiki,
        ];

        // Добавляем опциональные поля только если они заданы
        if ($this->gitignoreTemplate !== null) {
            $data['gitignore_template'] = $this->gitignoreTemplate;
        }

        if ($this->licenseTemplate !== null) {
            $data['license_template'] = $this->licenseTemplate;
        }

        return $data;
    }
}
```

```php
<?php

namespace App\Http\Clients\GitHub\Requests;

use App\Http\Clients\GitHub\Attributes\CreateRepositoryAttribute;
use App\Http\Clients\GitHub\Responses\BadResponse;
use App\Http\Clients\GitHub\Responses\CreateRepositoryResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Response;

/**
 * Запрос для создания репозитория в GitHub
 */
class CreateRepositoryRequest
{
    public function __construct(
        public Factory $client,
        protected string $baseUrl = 'https://api.github.com',
        protected ?string $accessToken = null
    ) {}

    public function send(CreateRepositoryAttribute $attribute): BadResponse|CreateRepositoryResponse
    {
        // Проверка наличия токена авторизации
        if (empty($this->accessToken)) {
            throw new \RuntimeException('Токен доступа GitHub обязателен для создания репозитория');
        }

        $response = $this->client
            ->timeout(60) // Увеличенный таймаут для POST запросов
            ->withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Accept' => 'application/vnd.github.v3+json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/user/repos", $attribute->toArray());

        if ($response->status() === Response::HTTP_CREATED) {
            return CreateRepositoryResponse::fromResponse($response);
        }

        return BadResponse::fromResponse($response);
    }
}
```

### 3. Работа с заголовками

```php
<?php

namespace App\Http\Clients\GitHub\Requests;

use Illuminate\Http\Client\Factory;

/**
 * Базовый класс для всех GitHub запросов с общей логикой заголовков
 * 
 * Архитектурное решение: Вынесение общей логики работы с заголовками
 * в базовый класс позволяет избежать дублирования кода и обеспечивает
 * единообразие в обработке авторизации и других заголовков.
 */
abstract class BaseGitHubRequest
{
    protected array $defaultHeaders = [
        'Accept' => 'application/vnd.github.v3+json',
        'User-Agent' => 'Laravel-HTTP-Client/1.0',
    ];

    public function __construct(
        public Factory $client,
        protected string $baseUrl = 'https://api.github.com',
        protected ?string $accessToken = null
    ) {}

    /**
     * Получение заголовков для запроса
     * 
     * @param array<string, string> $additionalHeaders Дополнительные заголовки
     * @return array<string, string>
     */
    protected function getHeaders(array $additionalHeaders = []): array
    {
        $headers = $this->defaultHeaders;

        // Добавление токена авторизации если он есть
        if (!empty($this->accessToken)) {
            $headers['Authorization'] = "Bearer {$this->accessToken}";
        }

        // Объединение с дополнительными заголовками
        return array_merge($headers, $additionalHeaders);
    }

    /**
     * Настройка HTTP клиента с общими параметрами
     * 
     * @param array<string, string> $additionalHeaders
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function prepareClient(array $additionalHeaders = []): \Illuminate\Http\Client\PendingRequest
    {
        return $this->client
            ->timeout(30)
            ->retry(3, 1000)
            ->withHeaders($this->getHeaders($additionalHeaders));
    }
}
```

### 4. Обработка ответов

```php
<?php

namespace App\Http\Clients\GitHub\Responses;

use Illuminate\Http\Client\Response;

/**
 * Класс для обработки успешного ответа получения пользователя
 * 
 * Архитектурное решение: Инкапсуляция логики обработки ответа в отдельном классе
 * обеспечивает типобезопасность и упрощает работу с данными ответа.
 */
class GetUserResponse
{
    private function __construct(
        public Response $original,
        public int $status,
        public int $id,
        public string $login,
        public string $name,
        public ?string $email,
        public ?string $bio,
        public int $publicRepos,
        public int $followers,
        public int $following,
        public \DateTimeImmutable $createdAt
    ) {}

    /**
     * Создание объекта ответа из HTTP ответа
     * 
     * @param Response $response HTTP ответ
     * @return self
     * @throws \InvalidArgumentException При некорректных данных ответа
     */
    public static function fromResponse(Response $response): self
    {
        $data = $response->json();

        // Валидация обязательных полей
        if (!isset($data['id'], $data['login'])) {
            throw new \InvalidArgumentException('Ответ не содержит обязательных полей id и login');
        }

        return new self(
            original: $response,
            status: $response->status(),
            id: $data['id'],
            login: $data['login'],
            name: $data['name'] ?? '',
            email: $data['email'] ?? null,
            bio: $data['bio'] ?? null,
            publicRepos: $data['public_repos'] ?? 0,
            followers: $data['followers'] ?? 0,
            following: $data['following'] ?? 0,
            createdAt: new \DateTimeImmutable($data['created_at'])
        );
    }

    /**
     * Проверка успешности ответа
     */
    public function success(): bool
    {
        return true; // Если объект создан, значит ответ успешный
    }

    /**
     * Проверка на ошибку
     */
    public function bad(): bool
    {
        return !$this->success();
    }

    /**
     * Получение данных пользователя в виде массива
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'public_repos' => $this->publicRepos,
            'followers' => $this->followers,
            'following' => $this->following,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
```

### 5. Factory для централизованного управления HTTP клиентами

```php
<?php

namespace App\Http\Clients\GitHub\Factories;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

/**
 * Фабрика для создания настроенных HTTP клиентов GitHub API
 *
 * Архитектурное решение: Централизация конфигурации HTTP клиентов в фабрике
 * обеспечивает единообразие настроек, упрощает изменение конфигурации
 * и позволяет легко добавлять новые типы клиентов.
 */
class GitHubApiFactory
{
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_RETRIES = 3;
    private const RETRY_DELAY = 1000; // миллисекунды

    public function __construct(
        private Factory $httpFactory,
        private string $baseUrl = 'https://api.github.com',
        private ?string $userAgent = null
    ) {
        $this->userAgent = $userAgent ?? 'Laravel-HTTP-Client/' . app()->version();
    }

    /**
     * Создание базового HTTP клиента с общими настройками
     *
     * @return PendingRequest
     */
    public function make(): PendingRequest
    {
        return $this->httpFactory
            ->baseUrl($this->baseUrl)
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => $this->userAgent,
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->timeout(self::DEFAULT_TIMEOUT)
            ->retry(self::DEFAULT_RETRIES, self::RETRY_DELAY)
            ->withOptions([
                'verify' => true,
                'http_errors' => false, // Обрабатываем ошибки самостоятельно
            ])
            ->beforeSending(function ($request, $options) {
                // Логирование исходящих запросов
                Log::info('GitHub API Request', [
                    'method' => $options['method'] ?? 'GET',
                    'url' => $request->url(),
                    'headers' => $this->sanitizeHeaders($request->headers()),
                ]);
            });
    }

    /**
     * Создание клиента с токеном авторизации
     *
     * @param string $token Personal Access Token или OAuth token
     * @return PendingRequest
     */
    public function withAuth(string $token): PendingRequest
    {
        return $this->make()
            ->withToken($token, 'Bearer');
    }

    /**
     * Создание клиента с базовой авторизацией
     *
     * @param string $username
     * @param string $password
     * @return PendingRequest
     */
    public function withBasicAuth(string $username, string $password): PendingRequest
    {
        return $this->make()
            ->withBasicAuth($username, $password);
    }

    /**
     * Создание клиента для GitHub Apps
     *
     * @param string $appId ID приложения
     * @param string $privateKey Приватный ключ приложения
     * @param int $installationId ID установки приложения
     * @return PendingRequest
     */
    public function withGitHubApp(string $appId, string $privateKey, int $installationId): PendingRequest
    {
        $jwt = $this->generateJWT($appId, $privateKey);

        // Получаем installation token
        $installationToken = $this->getInstallationToken($jwt, $installationId);

        return $this->make()
            ->withToken($installationToken, 'Bearer');
    }

    /**
     * Создание клиента с повышенным таймаутом для длительных операций
     *
     * @param int $timeout Таймаут в секундах
     * @return PendingRequest
     */
    public function withExtendedTimeout(int $timeout = 120): PendingRequest
    {
        return $this->make()
            ->timeout($timeout);
    }

    /**
     * Создание клиента для отладки с подробным логированием
     *
     * @return PendingRequest
     */
    public function debug(): PendingRequest
    {
        return $this->make()
            ->withOptions(['debug' => true])
            ->beforeSending(function ($request, $options) {
                Log::debug('GitHub API Debug Request', [
                    'method' => $options['method'] ?? 'GET',
                    'url' => $request->url(),
                    'headers' => $request->headers(),
                    'body' => $options['body'] ?? null,
                ]);
            });
    }

    /**
     * Создание клиента для работы с GraphQL API
     *
     * @param string $token
     * @return PendingRequest
     */
    public function graphql(string $token): PendingRequest
    {
        return $this->httpFactory
            ->baseUrl('https://api.github.com/graphql')
            ->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'User-Agent' => $this->userAgent,
            ])
            ->timeout(self::DEFAULT_TIMEOUT)
            ->retry(self::DEFAULT_RETRIES, self::RETRY_DELAY);
    }

    /**
     * Создание клиента для загрузки файлов
     *
     * @param string $token
     * @return PendingRequest
     */
    public function upload(string $token): PendingRequest
    {
        return $this->make()
            ->withToken($token, 'Bearer')
            ->timeout(300) // 5 минут для загрузки файлов
            ->withHeaders([
                'Content-Type' => 'application/octet-stream',
            ]);
    }

    /**
     * Генерация JWT для GitHub App
     *
     * @param string $appId
     * @param string $privateKey
     * @return string
     */
    private function generateJWT(string $appId, string $privateKey): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
        $payload = json_encode([
            'iat' => time(),
            'exp' => time() + 600, // 10 минут
            'iss' => $appId,
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64Header . '.' . $base64Payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * Получение installation token для GitHub App
     *
     * @param string $jwt
     * @param int $installationId
     * @return string
     */
    private function getInstallationToken(string $jwt, int $installationId): string
    {
        $response = $this->httpFactory
            ->withHeaders([
                'Authorization' => "Bearer {$jwt}",
                'Accept' => 'application/vnd.github.v3+json',
            ])
            ->post("{$this->baseUrl}/app/installations/{$installationId}/access_tokens");

        if (!$response->successful()) {
            throw new \RuntimeException('Не удалось получить installation token: ' . $response->body());
        }

        return $response->json('token');
    }

    /**
     * Санитизация заголовков для логирования
     *
     * @param array $headers
     * @return array
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-api-key', 'x-secret-key'];

        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), $sensitiveHeaders, true)) {
                $headers[$name] = '***HIDDEN***';
            }
        }

        return $headers;
    }
}
```

### 6. Использование Factory в Request классах

```php
<?php

namespace App\Http\Clients\GitHub\Requests;

use App\Http\Clients\GitHub\Attributes\GetUserAttribute;
use App\Http\Clients\GitHub\Factories\GitHubApiFactory;
use App\Http\Clients\GitHub\Responses\BadResponse;
use App\Http\Clients\GitHub\Responses\GetUserResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

/**
 * Запрос для получения информации о пользователе GitHub с использованием Factory
 *
 * Архитектурное решение: Использование Factory в Request классах обеспечивает
 * единообразную конфигурацию HTTP клиентов и упрощает управление авторизацией.
 */
class GetUserRequest
{
    public function __construct(
        private GitHubApiFactory $factory,
        private ?string $accessToken = null
    ) {}

    /**
     * Выполнение запроса получения пользователя
     *
     * @param GetUserAttribute $attribute
     * @return BadResponse|GetUserResponse
     */
    public function send(GetUserAttribute $attribute): BadResponse|GetUserResponse
    {
        try {
            // Создание HTTP клиента через Factory
            $client = $this->createClient();

            // Выполнение запроса
            $response = $client->get("/users/{$attribute->toUrlParams()['username']}");

            // Логирование ответа
            Log::info('GitHub API Response', [
                'status' => $response->status(),
                'username' => $attribute->toUrlParams()['username'],
                'response_time' => $response->transferStats?->getTransferTime(),
            ]);

            // Обработка ответа
            if ($response->successful()) {
                return GetUserResponse::fromResponse($response);
            }

            return BadResponse::fromResponse($response);

        } catch (\Exception $e) {
            Log::error('GitHub API Request Error', [
                'error' => $e->getMessage(),
                'username' => $attribute->toUrlParams()['username'],
            ]);

            throw $e;
        }
    }

    /**
     * Создание настроенного HTTP клиента
     *
     * @return PendingRequest
     */
    private function createClient(): PendingRequest
    {
        // Если есть токен авторизации, используем его
        if ($this->accessToken) {
            return $this->factory->withAuth($this->accessToken);
        }

        // Иначе используем базовый клиент
        return $this->factory->make();
    }

    /**
     * Установка токена авторизации
     *
     * @param string $token
     * @return self
     */
    public function withToken(string $token): self
    {
        $this->accessToken = $token;
        return $this;
    }
}
```

### 7. Комплексный пример с Factory и Service Layer

```php
<?php

namespace App\Http\Clients\GitHub\Services;

use App\Http\Clients\GitHub\Attributes\CreateRepositoryAttribute;
use App\Http\Clients\GitHub\Attributes\GetUserAttribute;
use App\Http\Clients\GitHub\Factories\GitHubApiFactory;
use App\Http\Clients\GitHub\Requests\CreateRepositoryRequest;
use App\Http\Clients\GitHub\Requests\GetUserRequest;
use App\Http\Clients\GitHub\Responses\BadResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с GitHub API с использованием Factory
 *
 * Архитектурное решение: Service Layer инкапсулирует бизнес-логику
 * и координирует работу между различными Request классами,
 * используя единую Factory для создания HTTP клиентов.
 */
class GitHubService
{
    public function __construct(
        private GitHubApiFactory $factory,
        private string $accessToken
    ) {}

    /**
     * Получение информации о пользователе с кэшированием
     *
     * @param string $username
     * @return array
     * @throws \Exception
     */
    public function getUserInfo(string $username): array
    {
        $cacheKey = "github_user_{$username}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($username) {
            $attribute = new GetUserAttribute($username);
            $request = new GetUserRequest($this->factory, $this->accessToken);

            $response = $request->send($attribute);

            if ($response instanceof BadResponse) {
                throw new \RuntimeException("Не удалось получить пользователя: {$response->message}");
            }

            return $response->toArray();
        });
    }

    /**
     * Создание репозитория с проверкой существования
     *
     * @param string $name
     * @param string $description
     * @param bool $private
     * @return array
     * @throws \Exception
     */
    public function createRepository(string $name, string $description = '', bool $private = false): array
    {
        // Проверяем, не существует ли уже репозиторий
        if ($this->repositoryExists($name)) {
            throw new \InvalidArgumentException("Репозиторий '{$name}' уже существует");
        }

        $attribute = new CreateRepositoryAttribute(
            name: $name,
            description: $description,
            private: $private
        );

        $request = new CreateRepositoryRequest($this->factory, $this->accessToken);
        $response = $request->send($attribute);

        if ($response instanceof BadResponse) {
            Log::error('GitHub: Ошибка создания репозитория', [
                'name' => $name,
                'error' => $response->message,
                'status' => $response->status,
            ]);

            throw new \RuntimeException("Не удалось создать репозиторий: {$response->message}");
        }

        Log::info('GitHub: Репозиторий успешно создан', [
            'name' => $name,
            'private' => $private,
        ]);

        return $response->toArray();
    }

    /**
     * Проверка существования репозитория
     *
     * @param string $name
     * @return bool
     */
    private function repositoryExists(string $name): bool
    {
        try {
            $client = $this->factory->withAuth($this->accessToken);
            $response = $client->get("/repos/{$this->getCurrentUser()['login']}/{$name}");

            return $response->status() === 200;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Получение информации о текущем пользователе
     *
     * @return array
     */
    private function getCurrentUser(): array
    {
        static $currentUser = null;

        if ($currentUser === null) {
            $client = $this->factory->withAuth($this->accessToken);
            $response = $client->get('/user');

            if (!$response->successful()) {
                throw new \RuntimeException('Не удалось получить информацию о текущем пользователе');
            }

            $currentUser = $response->json();
        }

        return $currentUser;
    }

    /**
     * Массовое создание репозиториев с обработкой ошибок
     *
     * @param array $repositories Массив данных репозиториев
     * @return array Результаты создания
     */
    public function createMultipleRepositories(array $repositories): array
    {
        $results = [];
        $errors = [];

        foreach ($repositories as $index => $repoData) {
            try {
                $result = $this->createRepository(
                    name: $repoData['name'],
                    description: $repoData['description'] ?? '',
                    private: $repoData['private'] ?? false
                );

                $results[] = [
                    'index' => $index,
                    'name' => $repoData['name'],
                    'status' => 'success',
                    'data' => $result,
                ];

                // Небольшая задержка между запросами для соблюдения rate limits
                usleep(500000); // 0.5 секунды

            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'name' => $repoData['name'],
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];

                Log::warning('GitHub: Ошибка при массовом создании репозитория', [
                    'name' => $repoData['name'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'successful' => $results,
            'failed' => $errors,
            'summary' => [
                'total' => count($repositories),
                'successful_count' => count($results),
                'failed_count' => count($errors),
            ],
        ];
    }

    /**
     * Получение статистики пользователя
     *
     * @param string $username
     * @return array
     */
    public function getUserStatistics(string $username): array
    {
        $userInfo = $this->getUserInfo($username);

        // Дополнительные запросы для получения статистики
        $client = $this->factory->withAuth($this->accessToken);

        // Получение репозиториев
        $reposResponse = $client->get("/users/{$username}/repos", [
            'per_page' => 100,
            'sort' => 'updated',
        ]);

        $repositories = $reposResponse->successful() ? $reposResponse->json() : [];

        // Получение подписчиков
        $followersResponse = $client->get("/users/{$username}/followers", [
            'per_page' => 1,
        ]);

        $followersCount = $followersResponse->successful()
            ? count($followersResponse->json())
            : $userInfo['followers'];

        return [
            'user' => $userInfo,
            'statistics' => [
                'total_repositories' => count($repositories),
                'public_repositories' => $userInfo['public_repos'],
                'followers' => $followersCount,
                'following' => $userInfo['following'],
                'most_used_languages' => $this->extractLanguages($repositories),
                'account_age_days' => now()->diffInDays($userInfo['created_at']),
            ],
        ];
    }

    /**
     * Извлечение языков программирования из репозиториев
     *
     * @param array $repositories
     * @return array
     */
    private function extractLanguages(array $repositories): array
    {
        $languages = [];

        foreach ($repositories as $repo) {
            if (!empty($repo['language'])) {
                $languages[$repo['language']] = ($languages[$repo['language']] ?? 0) + 1;
            }
        }

        arsort($languages);

        return array_slice($languages, 0, 5, true); // Топ 5 языков
    }
}
```

### 8. Пример использования Service с Factory в контроллере

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Clients\GitHub\Factories\GitHubApiFactory;
use App\Http\Clients\GitHub\Services\GitHubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер для работы с GitHub API
 *
 * Демонстрирует использование Service Layer с Factory
 * для создания чистого и тестируемого API.
 */
class GitHubController extends Controller
{
    public function __construct(
        private GitHubApiFactory $factory
    ) {}

    /**
     * Получение информации о пользователе
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function getUser(Request $request, string $username): JsonResponse
    {
        try {
            $service = new GitHubService(
                $this->factory,
                $request->bearerToken() ?? config('services.github.token')
            );

            $userInfo = $service->getUserInfo($username);

            return response()->json([
                'success' => true,
                'data' => $userInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Создание репозитория
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createRepository(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|regex:/^[a-zA-Z0-9\-_.]+$/',
            'description' => 'nullable|string|max:500',
            'private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $service = new GitHubService(
                $this->factory,
                $request->bearerToken() ?? config('services.github.token')
            );

            $repository = $service->createRepository(
                name: $request->input('name'),
                description: $request->input('description', ''),
                private: $request->boolean('private', false)
            );

            return response()->json([
                'success' => true,
                'data' => $repository,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Получение статистики пользователя
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function getUserStatistics(Request $request, string $username): JsonResponse
    {
        try {
            $service = new GitHubService(
                $this->factory,
                $request->bearerToken() ?? config('services.github.token')
            );

            $statistics = $service->getUserStatistics($username);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### 9. Регистрация Factory в Service Provider

```php
<?php

namespace App\Providers;

use App\Http\Clients\GitHub\Factories\GitHubApiFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider для регистрации HTTP клиентов
 *
 * Архитектурное решение: Регистрация Factory в Service Provider
 * обеспечивает правильное внедрение зависимостей и централизованную
 * конфигурацию всех HTTP клиентов приложения.
 */
class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов
     */
    public function register(): void
    {
        // Регистрация GitHub API Factory
        $this->app->singleton(GitHubApiFactory::class, function ($app) {
            return new GitHubApiFactory(
                httpFactory: $app->make(HttpFactory::class),
                baseUrl: config('services.github.base_url', 'https://api.github.com'),
                userAgent: config('services.github.user_agent', 'Laravel-App/1.0')
            );
        });

        // Регистрация других API Factory по аналогии
        // $this->registerTwitterApiFactory();
        // $this->registerSlackApiFactory();
    }

    /**
     * Загрузка сервисов
     */
    public function boot(): void
    {
        // Публикация конфигурации для HTTP клиентов
        $this->publishes([
            __DIR__.'/../../config/http-clients.php' => config_path('http-clients.php'),
        ], 'http-clients-config');
    }

    /**
     * Пример регистрации Twitter API Factory
     */
    private function registerTwitterApiFactory(): void
    {
        $this->app->singleton(\App\Http\Clients\Twitter\Factories\TwitterApiFactory::class, function ($app) {
            return new \App\Http\Clients\Twitter\Factories\TwitterApiFactory(
                httpFactory: $app->make(HttpFactory::class),
                baseUrl: config('services.twitter.base_url', 'https://api.twitter.com/2'),
                bearerToken: config('services.twitter.bearer_token')
            );
        });
    }
}
```

### 10. Конфигурационный файл для HTTP клиентов

```php
<?php

// config/http-clients.php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub API Configuration
    |--------------------------------------------------------------------------
    */
    'github' => [
        'base_url' => env('GITHUB_API_URL', 'https://api.github.com'),
        'user_agent' => env('GITHUB_USER_AGENT', 'Laravel-App/1.0'),
        'timeout' => env('GITHUB_TIMEOUT', 30),
        'retries' => env('GITHUB_RETRIES', 3),
        'retry_delay' => env('GITHUB_RETRY_DELAY', 1000),

        // Токены авторизации
        'personal_access_token' => env('GITHUB_PERSONAL_ACCESS_TOKEN'),

        // GitHub App настройки
        'app' => [
            'id' => env('GITHUB_APP_ID'),
            'private_key' => env('GITHUB_APP_PRIVATE_KEY'),
            'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
        ],

        // Rate limiting
        'rate_limit' => [
            'requests_per_hour' => env('GITHUB_RATE_LIMIT', 5000),
            'burst_limit' => env('GITHUB_BURST_LIMIT', 100),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'timeout' => env('HTTP_CLIENT_TIMEOUT', 30),
        'retries' => env('HTTP_CLIENT_RETRIES', 3),
        'retry_delay' => env('HTTP_CLIENT_RETRY_DELAY', 1000),
        'verify_ssl' => env('HTTP_CLIENT_VERIFY_SSL', true),
        'user_agent' => env('HTTP_CLIENT_USER_AGENT', 'Laravel-HTTP-Client/1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('HTTP_CLIENT_LOGGING_ENABLED', true),
        'log_requests' => env('HTTP_CLIENT_LOG_REQUESTS', true),
        'log_responses' => env('HTTP_CLIENT_LOG_RESPONSES', false),
        'log_errors' => env('HTTP_CLIENT_LOG_ERRORS', true),
        'sanitize_headers' => ['authorization', 'x-api-key', 'x-secret-key'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('HTTP_CLIENT_CACHE_ENABLED', true),
        'default_ttl' => env('HTTP_CLIENT_CACHE_TTL', 900), // 15 минут
        'prefix' => env('HTTP_CLIENT_CACHE_PREFIX', 'http_client'),
    ],
];
```

### 11. Пример использования в Artisan команде

```php
<?php

namespace App\Console\Commands;

use App\Http\Clients\GitHub\Factories\GitHubApiFactory;
use App\Http\Clients\GitHub\Services\GitHubService;
use Illuminate\Console\Command;

/**
 * Artisan команда для синхронизации данных с GitHub
 *
 * Демонстрирует использование Factory и Service в консольных командах
 */
class SyncGitHubDataCommand extends Command
{
    protected $signature = 'github:sync {username} {--token=}';
    protected $description = 'Синхронизация данных пользователя с GitHub API';

    public function __construct(
        private GitHubApiFactory $factory
    ) {
        parent::__construct();
    }

    /**
     * Выполнение команды
     */
    public function handle(): int
    {
        $username = $this->argument('username');
        $token = $this->option('token') ?? config('services.github.personal_access_token');

        if (empty($token)) {
            $this->error('Не указан токен авторизации GitHub');
            return self::FAILURE;
        }

        $this->info("Начинаем синхронизацию данных для пользователя: {$username}");

        try {
            $service = new GitHubService($this->factory, $token);

            // Получение базовой информации
            $this->line('Получение информации о пользователе...');
            $userInfo = $service->getUserInfo($username);
            $this->info("✓ Пользователь: {$userInfo['name']} ({$userInfo['login']})");

            // Получение статистики
            $this->line('Получение статистики...');
            $statistics = $service->getUserStatistics($username);

            $this->table(
                ['Метрика', 'Значение'],
                [
                    ['Публичные репозитории', $statistics['statistics']['public_repositories']],
                    ['Подписчики', $statistics['statistics']['followers']],
                    ['Подписки', $statistics['statistics']['following']],
                    ['Возраст аккаунта (дни)', $statistics['statistics']['account_age_days']],
                ]
            );

            // Отображение топ языков
            if (!empty($statistics['statistics']['most_used_languages'])) {
                $this->line('Наиболее используемые языки:');
                foreach ($statistics['statistics']['most_used_languages'] as $language => $count) {
                    $this->line("  • {$language}: {$count} репозиториев");
                }
            }

            $this->info('✓ Синхронизация завершена успешно');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Ошибка синхронизации: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
```

## 🔧 Комплексные примеры

### 1. HTTP клиент с системой авторизации

```php
<?php

namespace App\Http\Clients\GitHub\Services;

use App\Http\Clients\GitHub\Exceptions\AuthenticationException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для управления авторизацией в GitHub API
 *
 * Архитектурное решение: Централизованное управление токенами авторизации
 * с кэшированием и автоматическим обновлением обеспечивает надежность
 * и производительность при работе с API.
 */
class GitHubAuthService
{
    private const TOKEN_CACHE_KEY = 'github_access_token';
    private const TOKEN_CACHE_TTL = 3600; // 1 час

    public function __construct(
        private Factory $httpClient,
        private string $clientId,
        private string $clientSecret,
        private string $baseUrl = 'https://api.github.com'
    ) {}

    /**
     * Получение действующего токена доступа
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getAccessToken(): string
    {
        // Попытка получить токен из кэша
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cachedToken && $this->validateToken($cachedToken)) {
            return $cachedToken;
        }

        // Получение нового токена
        $newToken = $this->refreshAccessToken();

        // Кэширование нового токена
        Cache::put(self::TOKEN_CACHE_KEY, $newToken, self::TOKEN_CACHE_TTL);

        return $newToken;
    }

    /**
     * Обновление токена доступа
     *
     * @return string
     * @throws AuthenticationException
     */
    private function refreshAccessToken(): string
    {
        try {
            $response = $this->httpClient
                ->timeout(30)
                ->post('https://github.com/login/oauth/access_token', [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                throw new AuthenticationException(
                    'Не удалось получить токен доступа: ' . $response->body()
                );
            }

            $data = $response->json();

            if (!isset($data['access_token'])) {
                throw new AuthenticationException('Ответ не содержит токен доступа');
            }

            Log::info('GitHub: Получен новый токен доступа');

            return $data['access_token'];

        } catch (\Exception $e) {
            Log::error('GitHub: Ошибка при получении токена доступа', [
                'error' => $e->getMessage()
            ]);

            throw new AuthenticationException(
                'Ошибка авторизации: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Валидация токена доступа
     *
     * @param string $token
     * @return bool
     */
    private function validateToken(string $token): bool
    {
        try {
            $response = $this->httpClient
                ->withHeaders(['Authorization' => "Bearer {$token}"])
                ->get("{$this->baseUrl}/user");

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Очистка кэшированного токена
     */
    public function clearTokenCache(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }
}
```

### 2. Производительность

**Оптимизации**:
- Используйте connection pooling
- Настройте правильные таймауты
- Реализуйте circuit breaker pattern
- Мониторьте memory usage

```php
// Пример circuit breaker
class CircuitBreaker
{
    private int $failureCount = 0;
    private bool $isOpen = false;
    private \DateTimeImmutable $lastFailureTime;

    public function call(callable $operation): mixed
    {
        if ($this->isOpen && $this->shouldAttemptReset()) {
            $this->isOpen = false;
            $this->failureCount = 0;
        }

        if ($this->isOpen) {
            throw new CircuitBreakerOpenException('Circuit breaker is open');
        }

        try {
            $result = $operation();
            $this->onSuccess();
            return $result;
        } catch (\Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }
}
```

## 📝 Заключение

HTTP Client Generator для Laravel предоставляет мощную основу для создания надежных и масштабируемых HTTP клиентов. Следуя приведенным примерам и рекомендациям, вы сможете:

1. **Создавать структурированные HTTP клиенты** с четким разделением ответственности
2. **Обеспечивать надежность** через retry механизмы и обработку ошибок
3. **Поддерживать безопасность** через правильную авторизацию и валидацию
4. **Мониторить производительность** через логирование и метрики
5. **Упрощать тестирование** благодаря модульной архитектуре

Помните: хороший HTTP клиент - это не только работающий код, но и код, который легко поддерживать, тестировать и масштабировать.
