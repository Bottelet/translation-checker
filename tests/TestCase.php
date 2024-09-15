<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\Commands\CheckTranslation;
use Bottelet\TranslationChecker\TranslationCheckerServiceProvider;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\NoneExistingTranslator;
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
        if (! file_exists($this->tempDir)) {
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
            'model' => 'gpt-3.5-turbo',
            'api_key' => 'API_KEY',
            'organization_id' => 'ORG_ID',
        ]);

        $this->createTemplateFiles();
    }


    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [TranslationCheckerServiceProvider::class];
    }

    protected function tearDown(): void
    {
        $iterator    = new RecursiveDirectoryIterator($this->tempDir, FilesystemIterator::SKIP_DOTS);
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
            file_get_contents(__DIR__.'/Files/templates/underscore-translations.blade.php'),
        );

        $this->phpControllerFile = $this->createTempFile(
            'TestController.php',
            file_get_contents(__DIR__.'/Files/templates/TestController.php'),
        );

        $this->vueFile = $this->createTempFile(
            'test.vue',
            file_get_contents(__DIR__.'/Files/templates/dollar-t.vue'),
        );

        $this->noTranslationsBladeFile = $this->createTempFile(
            'empty.blade.php',
            file_get_contents(__DIR__.'/Files/templates/no-translations.blade.php'),
        );
    }

    public function createTranslationFile(string $name, string|array $content = '')
    {
        $translationFile = $this->tempDir."/lang/{$name}.json";
        if ( ! file_exists(dirname($translationFile))) {
            mkdir(dirname($translationFile), 0777, true);
        }
        if(is_array($content)) {
            $content = json_encode($content);
        }
        file_put_contents($translationFile, $content);

        return $translationFile;
    }
}
