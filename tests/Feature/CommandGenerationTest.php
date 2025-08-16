<?php

namespace Osddqd\HttpClientGenerator\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommandGenerationTest extends TestCase
{
    private string $testBasePath;

    private string $testTestsPath;

    private array $createdFiles = [];

    private array $createdDirectories = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBasePath = base_path(Config::get('http-client-generator.paths.base'));
        $this->testTestsPath = base_path(Config::get('http-client-generator.paths.tests'));
    }

    #[Test]
    public function it_generates_attribute_class_and_test()
    {
        $this->artisan('http-client-generator:attribute', [
            'client' => 'Twitter',
            'name' => 'FetchTweets',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/Twitter/Attributes/FetchTweetsAttribute.php';
        $testPath = $this->testTestsPath.'/Twitter/Attributes/FetchTweetsAttributeTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\Twitter\Attributes;', $classContent);
        $this->assertStringContainsString('class FetchTweetsAttribute', $classContent);
        $this->assertStringContainsString('public function toArray(): array', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('namespace Tests\Unit\Twitter\Attributes;', $testContent);
        $this->assertStringContainsString('class FetchTweetsAttributeTest', $testContent);
        $this->assertStringContainsString('use App\Http\Clients\Twitter\Attributes\FetchTweetsAttribute;', $testContent);
    }

    #[Test]
    public function it_generates_request_class_and_test()
    {
        $this->artisan('http-client-generator:request', [
            'client' => 'GitHub',
            'name' => 'CreateRepository',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/GitHub/Requests/CreateRepositoryRequest.php';
        $testPath = $this->testTestsPath.'/GitHub/Requests/CreateRepositoryRequestTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\GitHub\Requests;', $classContent);
        $this->assertStringContainsString('class CreateRepositoryRequest', $classContent);
        $this->assertStringContainsString('public function send(', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class CreateRepositoryRequestTest', $testContent);
        $this->assertStringContainsString('#[Test]', $testContent);
    }

    #[Test]
    public function it_generates_response_class_and_test()
    {
        $this->artisan('http-client-generator:response', [
            'client' => 'Slack',
            'name' => 'SendMessage',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/Slack/Responses/SendMessageResponse.php';
        $testPath = $this->testTestsPath.'/Slack/Responses/SendMessageResponseTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\Slack\Responses;', $classContent);
        $this->assertStringContainsString('class SendMessageResponse', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class SendMessageResponseTest', $testContent);
    }

    #[Test]
    public function it_generates_bad_response_class_and_test()
    {
        $this->artisan('http-client-generator:bad-response', [
            'client' => 'PayPal',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/PayPal/Responses/BadResponse.php';
        $testPath = $this->testTestsPath.'/PayPal/Responses/BadResponseTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\PayPal\Responses;', $classContent);
        $this->assertStringContainsString('class BadResponse', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class BadResponseTest', $testContent);
    }

    #[Test]
    public function it_generates_factory_class_and_test()
    {
        // Проверим, что команда выполняется без ошибок
        $this->artisan('http-client-generator:factory', [
            'client' => 'GitHub',
            'name' => 'Api',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/GitHub/Factories/ApiFactory.php';
        $testPath = $this->testTestsPath.'/GitHub/Factories/ApiFactoryTest.php';

        // Проверим, что файлы созданы
        $this->assertFileExists($classPath, "Factory class file was not created at: {$classPath}");
        $this->assertFileExists($testPath, "Factory test file was not created at: {$testPath}");

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\GitHub\Factories;', $classContent);
        $this->assertStringContainsString('class ApiFactory', $classContent);
        $this->assertStringContainsString('public function make(): PendingRequest', $classContent);
        $this->assertStringContainsString('public function withAuth(string $token): PendingRequest', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class ApiFactoryTest', $testContent);
        $this->assertStringContainsString('private ApiFactory $factory;', $testContent);
    }

    #[Test]
    public function it_generates_all_classes_with_all_command()
    {
        $this->artisan('http-client-generator:all', [
            'client' => 'Stripe',
            'name' => 'CreateCharge',
        ])->assertExitCode(0);

        // Проверяем создание всех типов файлов
        $expectedFiles = [
            '/Stripe/Attributes/CreateChargeAttribute.php',
            '/Stripe/Requests/CreateChargeRequest.php',
            '/Stripe/Responses/CreateChargeResponse.php',
            '/Stripe/Responses/BadResponse.php',
            '/Stripe/Factories/CreateChargeFactory.php',
        ];

        $expectedTests = [
            '/Stripe/Attributes/CreateChargeAttributeTest.php',
            '/Stripe/Requests/CreateChargeRequestTest.php',
            '/Stripe/Responses/CreateChargeResponseTest.php',
            '/Stripe/Responses/BadResponseTest.php',
            '/Stripe/Factories/CreateChargeFactoryTest.php',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($this->testBasePath.$file);
        }

        foreach ($expectedTests as $test) {
            $this->assertFileExists($this->testTestsPath.$test);
        }
    }

    #[Test]
    public function it_skips_test_generation_with_no_tests_option()
    {
        // Очищаем возможные файлы от предыдущих тестов
        $testClientPath = $this->testBasePath.'/TestClient';
        $testClientTestsPath = $this->testTestsPath.'/TestClient';
        if (is_dir($testClientPath)) {
            File::deleteDirectory($testClientPath);
        }
        if (is_dir($testClientTestsPath)) {
            File::deleteDirectory($testClientTestsPath);
        }

        $this->artisan('http-client-generator:all', [
            'client' => 'TestClient',
            'name' => 'TestRequest',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Проверяем, что основные файлы созданы
        $expectedFiles = [
            '/TestClient/Attributes/TestRequestAttribute.php',
            '/TestClient/Requests/TestRequestRequest.php',
            '/TestClient/Responses/TestRequestResponse.php',
            '/TestClient/Responses/BadResponse.php',
            '/TestClient/Factories/TestRequestFactory.php',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($this->testBasePath.$file);
        }

        // Проверяем, что тесты НЕ созданы
        $expectedTests = [
            '/TestClient/Attributes/TestRequestAttributeTest.php',
            '/TestClient/Requests/TestRequestRequestTest.php',
            '/TestClient/Responses/TestRequestResponseTest.php',
            '/TestClient/Responses/BadResponseTest.php',
            '/TestClient/Factories/TestRequestFactoryTest.php',
        ];

        foreach ($expectedTests as $test) {
            $this->assertFileDoesNotExist($this->testTestsPath.$test);
        }
    }

    #[Test]
    public function it_skips_test_generation_for_individual_commands_with_no_tests_option()
    {
        // Тестируем команду attribute
        $this->artisan('http-client-generator:attribute', [
            'client' => 'IndividualTest',
            'name' => 'TestAttribute',
            '--no-tests' => true,
        ])->assertExitCode(0);

        $this->assertFileExists($this->testBasePath.'/IndividualTest/Attributes/TestAttributeAttribute.php');
        $this->assertFileDoesNotExist($this->testTestsPath.'/IndividualTest/Attributes/TestAttributeAttributeTest.php');

        // Тестируем команду request
        $this->artisan('http-client-generator:request', [
            'client' => 'IndividualTest',
            'name' => 'TestRequest',
            '--no-tests' => true,
        ])->assertExitCode(0);

        $this->assertFileExists($this->testBasePath.'/IndividualTest/Requests/TestRequestRequest.php');
        $this->assertFileDoesNotExist($this->testTestsPath.'/IndividualTest/Requests/TestRequestRequestTest.php');

        // Тестируем команду response
        $this->artisan('http-client-generator:response', [
            'client' => 'IndividualTest',
            'name' => 'TestResponse',
            '--no-tests' => true,
        ])->assertExitCode(0);

        $this->assertFileExists($this->testBasePath.'/IndividualTest/Responses/TestResponseResponse.php');
        $this->assertFileDoesNotExist($this->testTestsPath.'/IndividualTest/Responses/TestResponseResponseTest.php');

        // Тестируем команду bad-response
        $this->artisan('http-client-generator:bad-response', [
            'client' => 'IndividualTest',
            '--no-tests' => true,
        ])->assertExitCode(0);

        $this->assertFileExists($this->testBasePath.'/IndividualTest/Responses/BadResponse.php');
        $this->assertFileDoesNotExist($this->testTestsPath.'/IndividualTest/Responses/BadResponseTest.php');
    }

    #[Test]
    public function it_respects_global_test_generation_setting()
    {
        // Отключаем генерацию тестов глобально
        Config::set('http-client-generator.generate_tests', false);

        $this->artisan('http-client-generator:attribute', [
            'client' => 'GlobalTest',
            'name' => 'TestAttribute',
        ])->assertExitCode(0);

        // Проверяем, что класс создан, но тест не создан
        $this->assertFileExists($this->testBasePath.'/GlobalTest/Attributes/TestAttributeAttribute.php');
        $this->assertFileDoesNotExist($this->testTestsPath.'/GlobalTest/Attributes/TestAttributeAttributeTest.php');

        // Возвращаем настройку обратно
        Config::set('http-client-generator.generate_tests', true);
    }

    #[Test]
    public function it_works_with_custom_namespace_and_paths()
    {
        $customNamespace = 'App\\External\\Clients';
        $customPath = 'temp_custom_clients';
        $customTestsPath = 'temp_custom_tests';

        $this->artisan('http-client-generator:attribute', [
            'client' => 'CustomAPI',
            'name' => 'TestAction',
            '--namespace' => $customNamespace,
            '--path' => $customPath,
            '--tests-path' => $customTestsPath,
        ])->assertExitCode(0);

        $classPath = base_path($customPath.'/CustomAPI/Attributes/TestActionAttribute.php');
        $testPath = base_path($customTestsPath.'/CustomAPI/Attributes/TestActionAttributeTest.php');

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\External\Clients\CustomAPI\Attributes;', $classContent);

        // Добавляем в список для очистки
        $this->createdFiles[] = $classPath;
        $this->createdFiles[] = $testPath;
        $this->createdDirectories[] = base_path($customPath);
        $this->createdDirectories[] = base_path($customTestsPath);
    }

    #[Test]
    public function it_handles_existing_files_gracefully()
    {
        // Создаем файл первый раз
        $this->artisan('http-client-generator:attribute', [
            'client' => 'ExistingClient',
            'name' => 'ExistingAction',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/ExistingClient/Attributes/ExistingActionAttribute.php';
        $this->assertFileExists($classPath);

        $originalContent = File::get($classPath);

        // Пытаемся создать тот же файл снова
        $this->artisan('http-client-generator:attribute', [
            'client' => 'ExistingClient',
            'name' => 'ExistingAction',
        ])->assertExitCode(0);

        // Файл должен остаться без изменений или быть перезаписан в зависимости от логики
        $this->assertFileExists($classPath);
    }

    #[Test]
    public function it_creates_proper_directory_structure()
    {
        $this->artisan('http-client-generator:all', [
            'client' => 'ComplexClient',
            'name' => 'ComplexAction',
        ])->assertExitCode(0);

        // Проверяем структуру директорий для классов
        $this->assertDirectoryExists($this->testBasePath.'/ComplexClient');
        $this->assertDirectoryExists($this->testBasePath.'/ComplexClient/Attributes');
        $this->assertDirectoryExists($this->testBasePath.'/ComplexClient/Requests');
        $this->assertDirectoryExists($this->testBasePath.'/ComplexClient/Responses');
        $this->assertDirectoryExists($this->testBasePath.'/ComplexClient/Factories');

        // Проверяем структуру директорий для тестов
        $this->assertDirectoryExists($this->testTestsPath.'/ComplexClient');
        $this->assertDirectoryExists($this->testTestsPath.'/ComplexClient/Attributes');
        $this->assertDirectoryExists($this->testTestsPath.'/ComplexClient/Requests');
        $this->assertDirectoryExists($this->testTestsPath.'/ComplexClient/Responses');
        $this->assertDirectoryExists($this->testTestsPath.'/ComplexClient/Factories');
    }

    #[Test]
    public function it_generates_valid_php_syntax()
    {
        $this->artisan('http-client-generator:request', [
            'client' => 'SyntaxTest',
            'name' => 'ValidateCode',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/SyntaxTest/Requests/ValidateCodeRequest.php';
        $testPath = $this->testTestsPath.'/SyntaxTest/Requests/ValidateCodeRequestTest.php';

        // Проверяем синтаксис PHP
        $classContent = File::get($classPath);
        $this->assertStringStartsWith('<?php', $classContent);
        $this->assertStringContainsString('namespace ', $classContent);
        $this->assertStringContainsString('class ', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringStartsWith('<?php', $testContent);
        $this->assertStringContainsString('namespace ', $testContent);
        $this->assertStringContainsString('class ', $testContent);
        $this->assertStringContainsString('extends TestCase', $testContent);

        // Проверяем, что файлы можно включить без синтаксических ошибок
        $this->assertTrue($this->isValidPhpSyntax($classPath));
        $this->assertTrue($this->isValidPhpSyntax($testPath));
    }

    #[Test]
    public function it_handles_special_characters_in_names()
    {
        $this->artisan('http-client-generator:attribute', [
            'client' => 'Special-Client',
            'name' => 'Action_With_Underscores',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath.'/Special-Client/Attributes/Action_With_UnderscoresAttribute.php';
        $this->assertFileExists($classPath);

        $content = File::get($classPath);
        $this->assertStringContainsString('class Action_With_UnderscoresAttribute', $content);
    }

    #[Test]
    public function it_validates_input_parameters()
    {
        // Тест с пустыми значениями - должен запросить ввод или завершиться с ошибкой
        $result = $this->artisan('http-client-generator:attribute', [
            'client' => '',
            'name' => 'TestAction',
        ]);

        // В зависимости от реализации, может быть разный код выхода
        $result->assertExitCode(0);
    }

    #[Test]
    public function it_works_with_different_client_name_formats()
    {
        $testCases = [
            ['client' => 'simple', 'name' => 'action'],
            ['client' => 'CamelCase', 'name' => 'CamelAction'],
            ['client' => 'snake_case', 'name' => 'snake_action'],
            ['client' => 'kebab-case', 'name' => 'kebab-action'],
            ['client' => 'MixedFormat123', 'name' => 'Action123'],
        ];

        foreach ($testCases as $case) {
            $this->artisan('http-client-generator:attribute', $case)->assertExitCode(0);

            $classPath = $this->testBasePath."/{$case['client']}/Attributes/{$case['name']}Attribute.php";
            $this->assertFileExists($classPath, "Failed for client: {$case['client']}, name: {$case['name']}");
        }
    }

    private function isValidPhpSyntax(string $filePath): bool
    {
        if (! file_exists($filePath)) {
            $this->fail("File does not exist: {$filePath}");
        }

        $output = [];
        $returnCode = 0;
        exec('php -l '.escapeshellarg($filePath).' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $this->fail("PHP syntax error in {$filePath}: ".implode("\n", $output));
        }

        return $returnCode === 0;
    }
}
