<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\Exception\TranslationServiceException;
use Bottelet\TranslationChecker\Sort\AlphabeticSort;
use Bottelet\TranslationChecker\TranslationManager;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class KeyBasedTranslationTest extends TestCase
{
    protected string $testDir;
    protected string $jsonFilePath;
    protected TranslationManager $translationManager;
    protected MockObject $translationServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDir                = sys_get_temp_dir() . '/key-based-translation-test';
        if (! file_exists($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }
        $this->translationServiceMock = $this->createMock(GoogleTranslator::class);
        $this->translationManager     = new TranslationManager(
            new AlphabeticSort,
            $this->translationServiceMock
        );
    }

    protected function tearDown(): void
    {
        $it    = new RecursiveDirectoryIterator($this->testDir, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->testDir);

        parent::tearDown();
    }

    #[Test]
    public function translationsUsesKeysForIndicationOfFileToUse(): void
    {
        file_put_contents($this->testDir.'/file1.php', '<?php  
            __(\'app.header.title\'); 
            __(\'app.header.subtitle\');
            __(\'app.header.content\');
            __(\'app.header.content.message\');
            __(\'app.header.content.sub_message\');
            __(\'app.main-content.header.title\');
            __(\'validation.create.required\');
            __(\'validation.create.min.header\'); 
            __(\'validation.create.min.message\');
            '

        );

        $appTranslationFile = $this->createPhpTranslationFile('da/app.php', [
            'header' => [
                'title' => 'Title',
                'subtitle' => 'Subtitle',
            ],
        ]);
        $validationFile = $this->createPhpTranslationFile('da/validation/create.php', [
                'required' => 'required',
                'min' => [
                    'header' => 'header',
                ],
        ]);

        $missingTranslations = $this->translationManager->updateTranslationsFromFile(
            [$this->testDir],
            $this->tempDir.'/lang/da'
        );

        $appFile = require $appTranslationFile;
        $validationFile = require $validationFile;
        $this->assertArrayHasKey('header', $appFile);
        $this->assertArrayHasKey('title', $appFile['header']);
        $this->assertArrayHasKey('subtitle', $appFile['header']);
        $this->assertArrayHasKey('content', $appFile['header']);
        $this->assertArrayHasKey('message', $appFile['header']['content']);
        $this->assertArrayHasKey('sub_message', $appFile['header']['content']);
        $this->assertArrayHasKey('title', $appFile['main-content']['header']);

        $this->assertArrayHasKey('required', $validationFile);
        $this->assertArrayHasKey('header', $validationFile['min']);
        $this->assertArrayHasKey('message', $validationFile['min']);
    }
}
