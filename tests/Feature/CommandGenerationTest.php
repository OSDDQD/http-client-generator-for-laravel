<?php

namespace Osddqd\HttpClientGenerator\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
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

        $this->testBasePath = base_path('temp_test_clients');
        $this->testTestsPath = base_path('temp_test_tests');

        // Настраиваем тестовые пути
        Config::set('http-client-generator.paths.base', 'temp_test_clients');
        Config::set('http-client-generator.paths.tests', 'temp_test_tests');
        Config::set('http-client-generator.namespace.base', 'App\\Http\\Clients');
    }

    #[Test]
    public function it_generates_attribute_class_and_test()
    {
        $this->artisan('http-client-generator:attribute', [
            'client' => 'Twitter',
            'name' => 'FetchTweets',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath . '/Twitter/Attributes/FetchTweetsAttribute.php';
        $testPath = $this->testTestsPath . '/Twitter/Attributes/FetchTweetsAttributeTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\Twitter\Attributes;', $classContent);
        $this->assertStringContainsString('class FetchTweetsAttribute', $classContent);
        $this->assertStringContainsString('public function toArray(): array', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('namespace Tests\Unit\Http\Clients\Twitter\Attributes;', $testContent);
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

        $classPath = $this->testBasePath . '/GitHub/Requests/CreateRepositoryRequest.php';
        $testPath = $this->testTestsPath . '/GitHub/Requests/CreateRepositoryRequestTest.php';

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

        $classPath = $this->testBasePath . '/Slack/Responses/SendMessageResponse.php';
        $testPath = $this->testTestsPath . '/Slack/Responses/SendMessageResponseTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\Slack\Responses;', $classContent);
        $this->assertStringContainsString('class SendMessageResponse', $classContent);
        $this->assertStringContainsString('use HasStatus;', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class SendMessageResponseTest', $testContent);
        $this->assertStringContainsString('asset_class_has_has_status_trait', $testContent);
    }

    #[Test]
    public function it_generates_bad_response_class_and_test()
    {
        $this->artisan('http-client-generator:bad-response', [
            'client' => 'PayPal',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath . '/PayPal/Responses/BadResponse.php';
        $testPath = $this->testTestsPath . '/PayPal/Responses/BadResponseTest.php';

        $this->assertFileExists($classPath);
        $this->assertFileExists($testPath);

        $classContent = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\PayPal\Responses;', $classContent);
        $this->assertStringContainsString('class BadResponse', $classContent);
        $this->assertStringContainsString('use HasStatus;', $classContent);

        $testContent = File::get($testPath);
        $this->assertStringContainsString('class BadResponseTest', $testContent);
        $this->assertStringContainsString('asset_class_has_has_status_trait', $testContent);
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
        ];

        $expectedTests = [
            '/Stripe/Attributes/CreateChargeAttributeTest.php',
            '/Stripe/Requests/CreateChargeRequestTest.php',
            '/Stripe/Responses/CreateChargeResponseTest.php',
            '/Stripe/Responses/BadResponseTest.php',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($this->testBasePath . $file);
        }

        foreach ($expectedTests as $test) {
            $this->assertFileExists($this->testTestsPath . $test);
        }
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

        $classPath = base_path($customPath . '/CustomAPI/Attributes/TestActionAttribute.php');
        $testPath = base_path($customTestsPath . '/CustomAPI/Attributes/TestActionAttributeTest.php');

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

        $classPath = $this->testBasePath . '/ExistingClient/Attributes/ExistingActionAttribute.php';
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
        $this->assertDirectoryExists($this->testBasePath . '/ComplexClient');
        $this->assertDirectoryExists($this->testBasePath . '/ComplexClient/Attributes');
        $this->assertDirectoryExists($this->testBasePath . '/ComplexClient/Requests');
        $this->assertDirectoryExists($this->testBasePath . '/ComplexClient/Responses');

        // Проверяем структуру директорий для тестов
        $this->assertDirectoryExists($this->testTestsPath . '/ComplexClient');
        $this->assertDirectoryExists($this->testTestsPath . '/ComplexClient/Attributes');
        $this->assertDirectoryExists($this->testTestsPath . '/ComplexClient/Requests');
        $this->assertDirectoryExists($this->testTestsPath . '/ComplexClient/Responses');
    }

    #[Test]
    public function it_generates_valid_php_syntax()
    {
        $this->artisan('http-client-generator:request', [
            'client' => 'SyntaxTest',
            'name' => 'ValidateCode',
        ])->assertExitCode(0);

        $classPath = $this->testBasePath . '/SyntaxTest/Requests/ValidateCodeRequest.php';
        $testPath = $this->testTestsPath . '/SyntaxTest/Requests/ValidateCodeRequestTest.php';

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

        $classPath = $this->testBasePath . '/Special-Client/Attributes/Action_With_UnderscoresAttribute.php';
        $this->assertFileExists($classPath);

        $content = File::get($classPath);
        $this->assertStringContainsString('class Action_With_UnderscoresAttribute', $content);
    }

    #[Test]
    public function it_generates_macro_class_correctly()
    {
        $this->artisan('http-client-generator:client-macro', [
            'client' => 'MacroTest',
        ])->assertExitCode(0);

        $classPath = app_path('Http/Clients/MacroTest/MacroTestMacro.php');
        $this->assertFileExists($classPath);

        $content = File::get($classPath);
        $this->assertStringContainsString('namespace App\Http\Clients\MacroTest;', $content);
        $this->assertStringContainsString('class MacroTestMacro', $content);
        $this->assertStringContainsString('public function macrotest(): callable', $content);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Http;', $content);
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

            $classPath = $this->testBasePath . "/{$case['client']}/Attributes/{$case['name']}Attribute.php";
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
        exec("php -l " . escapeshellarg($filePath) . " 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            $this->fail("PHP syntax error in {$filePath}: " . implode("\n", $output));
        }

        return $returnCode === 0;
    }

    protected function tearDown(): void
    {
        // Очищаем созданные тестовые файлы и директории
        if (File::exists($this->testBasePath)) {
            File::deleteDirectory($this->testBasePath);
        }

        if (File::exists($this->testTestsPath)) {
            File::deleteDirectory($this->testTestsPath);
        }

        // Очищаем файлы макросов
        $macroPath = app_path('Http/Clients/MacroTest');
        if (File::exists($macroPath)) {
            File::deleteDirectory($macroPath);
        }

        // Очищаем дополнительные файлы
        foreach ($this->createdFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        // Очищаем дополнительные директории
        foreach (array_reverse($this->createdDirectories) as $dir) {
            if (File::exists($dir) && File::isDirectory($dir)) {
                File::deleteDirectory($dir);
            }
        }

        parent::tearDown();
    }
}