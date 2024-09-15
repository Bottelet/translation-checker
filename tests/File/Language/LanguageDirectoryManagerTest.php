<?php

namespace Bottelet\TranslationChecker\Tests\File\Language;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LanguageDirectoryManagerTest extends TestCase
{
    private string $translationFile;
    private string $secondTranslationFile;
    private LanguageDirectoryManager $directoryManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translationFile = $this->createTranslationFile('da');
        $this->secondTranslationFile = $this->createTranslationFile('fr');

        $this->directoryManager = new LanguageDirectoryManager($this->tempDir . '/lang');
    }

    #[Test]
    public function getAllLanguageFiles(): void
    {
        $files = $this->directoryManager->getLanguageFiles();

        $this->assertCount(2, $files);
        $this->assertEquals($this->translationFile, $files[0]->getPathname());
        $this->assertEquals($this->secondTranslationFile, $files[1]->getPathname());
    }
}
