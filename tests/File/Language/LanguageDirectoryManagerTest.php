<?php

namespace Bottelet\TranslationChecker\Tests\File\Language;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class LanguageDirectoryManagerTest extends TestCase
{
    private string $translationFile;
    private string $secondTranslationFile;
    private LanguageDirectoryManager $directoryManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translationFile = $this->createJsonTranslationFile('da');
        $this->secondTranslationFile = $this->createJsonTranslationFile('fr');

        $this->directoryManager = new LanguageDirectoryManager($this->tempDir . '/lang');
    }

    #[Test]
    public function getAllLanguageFiles(): void
    {
        $files = $this->directoryManager->getLanguageFiles();

        usort($files, function (SplFileInfo $a, SplFileInfo $b) {
            return strcmp($a->getFilename(), $b->getFilename());
        });

        $this->assertCount(2, $files);
        $this->assertEquals(
            $this->normalizePath($this->translationFile),
            $this->normalizePath($files[0]->getPathname())
        );
        $this->assertEquals(
            $this->normalizePath($this->secondTranslationFile),
            $this->normalizePath($files[1]->getPathname())
        );
    }

    private function normalizePath(string $path): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return $path;
        }
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
