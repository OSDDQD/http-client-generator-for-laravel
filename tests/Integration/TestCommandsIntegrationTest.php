<?php

namespace osddqd\HttpClientGenerator\Tests\Integration;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use osddqd\HttpClientGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TestCommandsIntegrationTest extends TestCase
{
    private string $testBasePath;

    private string $testTestsPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Используем конфигурацию как в других тестах
        $this->testBasePath = base_path(Config::get('http-client-generator.paths.base'));
        $this->testTestsPath = base_path(Config::get('http-client-generator.paths.tests'));
    }

    #[Test]
    public function it_creates_complete_workflow_with_separate_test_commands()
    {
        $client = 'IntegrationTest';
        $name = 'CompleteWorkflow';

        // 1. Создаем все классы без тестов
        $this->artisan('http-client-generator:all', [
            'client' => $client,
            'name' => $name,
            '--no-tests' => true,
        ])->assertExitCode(0);

        // 2. Проверяем, что классы созданы
        $expectedClasses = [
            "{$this->testBasePath}/{$client}/Attributes/{$name}Attribute.php",
            "{$this->testBasePath}/{$client}/Requests/{$name}Request.php",
            "{$this->testBasePath}/{$client}/Responses/{$name}Response.php",
            "{$this->testBasePath}/{$client}/Responses/BadResponse.php",
            "{$this->testBasePath}/{$client}/Factories/{$name}Factory.php",
        ];

        foreach ($expectedClasses as $classFile) {
            $this->assertFileExists($classFile, "Class file should exist: {$classFile}");
        }

        // 3. Создаем все тесты отдельно
        $this->artisan('http-client-generator:test:all', [
            'client' => $client,
            'name' => $name,
        ])->assertExitCode(0);

        // 4. Проверяем, что тесты созданы
        $expectedTests = [
            "{$this->testTestsPath}/{$client}/Attributes/{$name}AttributeTest.php",
            "{$this->testTestsPath}/{$client}/Requests/{$name}RequestTest.php",
            "{$this->testTestsPath}/{$client}/Responses/{$name}ResponseTest.php",
            "{$this->testTestsPath}/{$client}/Responses/BadResponseTest.php",
            "{$this->testTestsPath}/{$client}/Factories/{$name}FactoryTest.php",
        ];

        foreach ($expectedTests as $testFile) {
            $this->assertFileExists($testFile, "Test file should exist: {$testFile}");
        }

        // 5. Проверяем содержимое одного из тестов
        $attributeTestContent = File::get("{$this->testTestsPath}/{$client}/Attributes/{$name}AttributeTest.php");
        $this->assertStringContainsString("class {$name}AttributeTest", $attributeTestContent);
        $this->assertStringContainsString("use App\Http\Clients\\{$client}\Attributes\\{$name}Attribute;", $attributeTestContent);
    }

    #[Test]
    public function it_handles_individual_test_creation()
    {
        $client = 'IndividualTest';
        $name = 'SingleAction';

        // 1. Создаем только один класс
        $this->artisan('http-client-generator:attribute', [
            'client' => $client,
            'name' => $name,
            '--no-tests' => true,
        ])->assertExitCode(0);

        // 2. Создаем тест для этого класса
        $this->artisan('http-client-generator:test:attribute', [
            'client' => $client,
            'name' => $name,
        ])->assertExitCode(0);

        // 3. Проверяем результат
        $classFile = "{$this->testBasePath}/{$client}/Attributes/{$name}Attribute.php";
        $testFile = "{$this->testTestsPath}/{$client}/Attributes/{$name}AttributeTest.php";

        $this->assertFileExists($classFile);
        $this->assertFileExists($testFile);

        // 4. Попытка создать тест для несуществующего класса должна завершиться ошибкой
        $this->artisan('http-client-generator:test:request', [
            'client' => $client,
            'name' => $name,
        ])->assertExitCode(1);
    }

    #[Test]
    public function it_prevents_duplicate_test_creation()
    {
        $client = 'DuplicateTest';
        $name = 'PreventDuplicate';

        // 1. Создаем класс и тест
        $this->artisan('http-client-generator:attribute', [
            'client' => $client,
            'name' => $name,
        ])->assertExitCode(0);

        // 2. Попытка создать тест снова должна пропустить создание
        $result = $this->artisan('http-client-generator:test:attribute', [
            'client' => $client,
            'name' => $name,
        ]);

        $result->assertExitCode(0);
        $result->expectsOutput("{$name}AttributeTest class already exists. Skipping.");
    }
}
