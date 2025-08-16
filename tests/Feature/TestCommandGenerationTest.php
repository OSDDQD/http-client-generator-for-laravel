<?php

namespace osddqd\HttpClientGenerator\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TestCommandGenerationTest extends TestCase
{
    private string $testBasePath;

    private string $testTestsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBasePath = base_path(Config::get('http-client-generator.paths.base'));
        $this->testTestsPath = base_path(Config::get('http-client-generator.paths.tests'));
    }

    #[Test]
    public function it_generates_attribute_test_for_existing_class()
    {
        // Сначала создаем класс атрибута
        $this->artisan('http-client-generator:attribute', [
            'client' => 'TestClient',
            'name' => 'TestAttribute',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Теперь создаем тест, используя FQDN
        $this->artisan('http-client-generator:test:attribute', [
            'class_fqdn' => 'App\\Http\\Clients\\TestClient\\Attributes\\TestAttributeAttribute',
        ])->assertExitCode(0);

        $testPath = $this->testTestsPath.'/Http/Clients/TestClient/Attributes/TestAttributeAttributeTest.php';
        $this->assertFileExists($testPath);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class TestAttributeAttributeTest', $testContent);
        $this->assertStringContainsString('use App\Http\Clients\TestClient\Attributes\TestAttributeAttribute;', $testContent);
    }

    #[Test]
    public function it_fails_when_attribute_class_does_not_exist()
    {
        $this->artisan('http-client-generator:test:attribute', [
            'class_fqdn' => 'App\\Http\\Clients\\NonExistentClient\\Attributes\\NonExistentAttribute',
        ])->assertExitCode(1);
    }

    #[Test]
    public function it_generates_request_test_for_existing_class()
    {
        // Сначала создаем класс запроса
        $this->artisan('http-client-generator:request', [
            'client' => 'TestClient',
            'name' => 'TestRequest',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Теперь создаем тест, используя FQDN
        $this->artisan('http-client-generator:test:request', [
            'class_fqdn' => 'App\\Http\\Clients\\TestClient\\Requests\\TestRequestRequest',
        ])->assertExitCode(0);

        $testPath = $this->testTestsPath.'/Http/Clients/TestClient/Requests/TestRequestRequestTest.php';
        $this->assertFileExists($testPath);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class TestRequestRequestTest', $testContent);
        $this->assertStringContainsString('use App\Http\Clients\TestClient\Requests\TestRequestRequest;', $testContent);
    }

    #[Test]
    public function it_generates_response_test_for_existing_class()
    {
        // Сначала создаем класс ответа
        $this->artisan('http-client-generator:response', [
            'client' => 'TestClient',
            'name' => 'TestResponse',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Теперь создаем тест, используя FQDN
        $this->artisan('http-client-generator:test:response', [
            'class_fqdn' => 'App\\Http\\Clients\\TestClient\\Responses\\TestResponseResponse',
        ])->assertExitCode(0);

        $testPath = $this->testTestsPath.'/Http/Clients/TestClient/Responses/TestResponseResponseTest.php';
        $this->assertFileExists($testPath);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class TestResponseResponseTest', $testContent);
        $this->assertStringContainsString('use App\Http\Clients\TestClient\Responses\TestResponseResponse;', $testContent);
    }

    #[Test]
    public function it_generates_factory_test_for_existing_class()
    {
        // Сначала создаем класс фабрики
        $this->artisan('http-client-generator:factory', [
            'client' => 'TestClient',
            'name' => 'TestFactory',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Теперь создаем тест, используя FQDN
        $this->artisan('http-client-generator:test:factory', [
            'class_fqdn' => 'App\\Http\\Clients\\TestClient\\Factories\\TestFactoryFactory',
        ])->assertExitCode(0);

        $testPath = $this->testTestsPath.'/Http/Clients/TestClient/Factories/TestFactoryFactoryTest.php';
        $this->assertFileExists($testPath);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class TestFactoryFactoryTest', $testContent);
        $this->assertStringContainsString('use App\Http\Clients\TestClient\Factories\TestFactoryFactory;', $testContent);
    }

    #[Test]
    public function it_generates_bad_response_test_for_existing_class()
    {
        // Сначала создаем класс BadResponse
        $this->artisan('http-client-generator:bad-response', [
            'client' => 'TestClient',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Теперь создаем тест, используя FQDN
        $this->artisan('http-client-generator:test:bad-response', [
            'class_fqdn' => 'App\\Http\\Clients\\TestClient\\Responses\\BadResponse',
        ])->assertExitCode(0);

        $testPath = $this->testTestsPath.'/Http/Clients/TestClient/Responses/BadResponseTest.php';
        $this->assertFileExists($testPath);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class BadResponseTest', $testContent);
        $this->assertStringContainsString('use App\Http\Clients\TestClient\Responses\BadResponse;', $testContent);
    }

    #[Test]
    public function it_generates_all_tests_for_existing_classes()
    {
        // Сначала создаем все классы без тестов
        $this->artisan('http-client-generator:all', [
            'client' => 'CompleteClient',
            'name' => 'CompleteAction',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Теперь создаем все тесты, используя базовый namespace
        $this->artisan('http-client-generator:test:all', [
            'base_namespace' => 'App\\Http\\Clients\\CompleteClient',
            'name' => 'CompleteAction',
        ])->assertExitCode(0);

        // Проверяем, что все тесты созданы
        $expectedTests = [
            '/Http/Clients/CompleteClient/Attributes/CompleteActionAttributeTest.php',
            '/Http/Clients/CompleteClient/Requests/CompleteActionRequestTest.php',
            '/Http/Clients/CompleteClient/Responses/CompleteActionResponseTest.php',
            '/Http/Clients/CompleteClient/Factories/CompleteActionFactoryTest.php',
            '/Http/Clients/CompleteClient/Responses/BadResponseTest.php',
        ];

        foreach ($expectedTests as $testFile) {
            $this->assertFileExists($this->testTestsPath.$testFile);
        }
    }

    #[Test]
    public function it_supports_custom_paths_and_namespaces()
    {
        // Создаем класс с кастомными настройками
        $this->artisan('http-client-generator:attribute', [
            'client' => 'CustomClient',
            'name' => 'CustomAttribute',
            '--namespace' => 'App\\External\\Clients',
            '--path' => 'app/External/Clients',
            '--tests-path' => 'tests/Unit/External/Clients',
            '--no-tests' => true,
        ])->assertExitCode(0);

        // Создаем тест, используя FQDN и кастомный namespace для тестов
        $this->artisan('http-client-generator:test:attribute', [
            'class_fqdn' => 'App\\External\\Clients\\CustomClient\\Attributes\\CustomAttributeAttribute',
            '--test-namespace' => 'Tests\\Unit\\External\\Clients',
        ])->assertExitCode(0);

        $testPath = base_path('tests/Unit/External/Clients/CustomAttributeAttributeTest.php');
        $this->assertFileExists($testPath);

        $testContent = File::get($testPath);
        // Проверяем, что файл создан с правильным классом и use statement
        $this->assertStringContainsString('class CustomAttributeAttributeTest', $testContent);
        $this->assertStringContainsString('use App\External\Clients\CustomClient\Attributes\CustomAttributeAttribute;', $testContent);
    }
}
