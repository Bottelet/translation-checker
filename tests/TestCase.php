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

        $this->app['config']->set('translator.default_translation_service', NoneExistingTranslator::class);
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

    public function createTemplateFiles(): void
    {
        $bladeFilePath = $this->tempDir . '/test.blade.php';
        $bladePath = __DIR__.'/Files/templates/underscore-translations.blade.php';
        file_put_contents($bladeFilePath, file_get_contents($bladePath));

        $phpController = $this->tempDir . '/TestController.php';
        $phpControllerPath = __DIR__.'/Files/templates/TestController.php';
        file_put_contents($phpController, file_get_contents($phpControllerPath));

        $vueFilePath = $this->tempDir . '/test.vue';
        $vuePath = __DIR__.'/Files/templates/dollar-t.vue';
        file_put_contents($vueFilePath, file_get_contents($vuePath));

        $noTranslationsFile = $this->tempDir . '/empty.blade.php';
        $noTranslationsPath = __DIR__.'/Files/templates/no-translations.blade.php';
        file_put_contents($noTranslationsFile, file_get_contents($noTranslationsPath));

        $this->bladeFile = new SplFileInfo($bladeFilePath);
        $this->phpControllerFile = new SplFileInfo($phpControllerPath);
        $this->vueFile = new SplFileInfo($vueFilePath);
        $this->noTranslationsBladeFile = new SplFileInfo($noTranslationsPath);

    }
}
