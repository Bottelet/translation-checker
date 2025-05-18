<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\TranslationCheckerServiceProvider;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public string $tempDir;
    protected SplFileInfo $bladeFile;
    protected SplFileInfo $phpControllerFile;
    protected SplFileInfo $vueFile;
    protected SplFileInfo $noTranslationsBladeFile;

    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL);
        $this->tempDir = sys_get_temp_dir() . '/bottelet-translation-checker-test';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $this->app['config']->set('translator.default', 'testing');
        $this->app['config']->set('translator.translators.testing', [
            'driver' => TestingTranslator::class,
        ]);
        $this->app['config']->set('translator.translators.google', [
            'type' => 'test',
            'project_id' => 'test',
            'private_key' => 'test',
            'client_email' => 'test',
            'client_x509_cert_url' => 'test',
        ]);
        $this->app['config']->set('translator.translators.openai', [
            'model' => 'gpt-4o-mini',
            'api_key' => 'API_KEY',
            'organization_id' => 'ORG_ID',
        ]);
        $this->app['config']->set('translator.persistent_keys', []);

        $this->createTemplateFiles();
    }

    protected function tearDown(): void
    {
        $iterator = new RecursiveDirectoryIterator($this->tempDir, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
        }

        //rmdir($this->tempDir);

        parent::tearDown();
    }

    public function createTempFile(string $filename, ?string $content = ''): SplFileInfo
    {
        $filePath = $this->tempDir . '/' . $filename;
        file_put_contents($filePath, $content);

        return new SplFileInfo($filePath);
    }

    public function createTemplateFiles(): void
    {
        $this->bladeFile = $this->createTempFile(
            'test.blade.php',
            file_get_contents(__DIR__ . '/Files/templates/underscore-translations.blade.php'),
        );

        $this->phpControllerFile = $this->createTempFile(
            'TestController.php',
            file_get_contents(__DIR__ . '/Files/templates/TestController.php'),
        );

        $this->vueFile = $this->createTempFile(
            'test.vue',
            file_get_contents(__DIR__ . '/Files/templates/dollar-t.vue'),
        );

        $this->noTranslationsBladeFile = $this->createTempFile(
            'empty.blade.php',
            file_get_contents(__DIR__ . '/Files/templates/no-translations.blade.php'),
        );
    }

    public function createJsonTranslationFile(string $name, string|array $content = ''): string
    {
        $translationFile = $this->tempDir . "/lang/{$name}.json";
        if (!file_exists(dirname($translationFile))) {
            mkdir(dirname($translationFile), 0777, true);
        }
        if (is_array($content)) {
            $content = json_encode($content);
        }
        file_put_contents($translationFile, $content);

        return $translationFile;
    }

    public function createPhpTranslationFile(string $fullPath, array $content = []): string
    {
        $translationFile = $this->tempDir . '/lang/' . $fullPath;
        if (!file_exists(dirname($translationFile))) {
            mkdir(dirname($translationFile), 0777, true);
        }

        file_put_contents($translationFile, '<?php return ' . var_export($content, true) . ';');

        return $translationFile;
    }

    public function createNestedTranslationFile(string $language, string $filename, array $content = []): string
    {
        $languageDir = $this->tempDir . '/lang/' . $language;
        if (!file_exists($languageDir)) {
            mkdir($languageDir, 0777, true);
        }

        $filePath = $languageDir . '/' . $filename . '.php';
        file_put_contents($filePath, "<?php\n\nreturn " . var_export($content, true) . ";\n");

        return $filePath;
    }

    public function assertNestedFileContains(string $language, string $filename, array $expectedContent): void
    {
        $filePath = $this->tempDir . '/lang/' . $language . '/' . $filename . '.php';
        $this->assertFileExists($filePath);
        $fileContents = require $filePath;

        foreach ($expectedContent as $key => $value) {
            $this->assertArrayHasKey($key, $fileContents);
            $this->assertEquals($value, $fileContents[$key]);
        }
    }

    public function assertNestedFileSorted(string $language, string $filename): void
    {
        $filePath = $this->tempDir . '/lang/' . $language . '/' . $filename . '.php';
        $this->assertFileExists($filePath);
        $fileContents = require $filePath;

        $keys = array_keys($fileContents);

        $sortedKeys = $keys;
        sort($sortedKeys);

        $this->assertSame($sortedKeys, $keys, "Keys in {$language}/{$filename}.php are not sorted alphabetically");
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [TranslationCheckerServiceProvider::class];
    }
}
