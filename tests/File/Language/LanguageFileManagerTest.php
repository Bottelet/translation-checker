<?php

namespace Bottelet\TranslationChecker\Tests\File\Language;

use Bottelet\TranslationChecker\File\Language\LanguageFileManager;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class LanguageFileManagerTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = $this->createTranslationFile('da', [
            'welcome' => 'Welcome',
            'farewell' => 'Goodbye',
        ]);

    }

    #[Test]
    public function readJsonFileReturnsArray(): void
    {
        $jsonManager = new LanguageFileManager($this->tempFile);
        $translations = $jsonManager->readJsonFile();
        $this->assertIsArray($translations);
        $this->assertCount(2, $translations);
    }

    #[Test]
    public function updateJsonFileUpdatesContentCorrectly(): void
    {
        $jsonManager = new LanguageFileManager($this->tempFile);
        $newTranslations = [
            'welcome' => 'Welcome to our application',
            'farewell' => 'See you soon',
            'greeting' => 'Hello, User',
        ];
        $jsonManager->updateJsonFile($newTranslations);

        $updatedContent = json_decode(file_get_contents($this->tempFile), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($newTranslations, $updatedContent);
    }

    #[Test]
    public function ensureLastLineDoesNotAddComma(): void
    {
        $jsonManager = new LanguageFileManager($this->tempFile);
        $translations = [
            'welcome' => 'Welcome to our application',
            'farewell' => 'See you soon',
            'greeting' => 'Hello User',
        ];
        $jsonManager->updateJsonFile($translations);
        $jsonContent = file_get_contents($this->tempFile);
        $lines = explode(PHP_EOL, $jsonContent);

        //remove last line as it's curly bracket
        array_pop($lines);
        $lastLine = end($lines);
        $this->assertStringNotContainsString(',', $lastLine);

        $jsonManager->updateJsonFile(['new' => 'add new']);
        $this->assertStringNotContainsString(',', $lastLine);

        $jsonContent = file_get_contents($this->tempFile);
        $lines = explode(PHP_EOL, $jsonContent);
        array_pop($lines);
        $lastLine = end($lines);

        $this->assertStringNotContainsString(',', $lastLine);
        $this->assertStringContainsString('add new', $lastLine);
    }

    #[Test]
    public function sortJsonFileSortsKeys(): void
    {
        $jsonManager = new LanguageFileManager($this->tempFile);
        $translations = [
            'b' => 'Second',
            'a' => 'First',
        ];
        $jsonManager->updateJsonFile($translations);
        $jsonManager->sortJsonFile();

        $sortedContent = json_decode(file_get_contents($this->tempFile), true);
        $expectedOrder = ['a' => 'First', 'b' => 'Second'];
        $this->assertEquals(array_keys($expectedOrder), array_keys($sortedContent));
    }

    #[Test]
    public function nonexistentFileReadReturnsEmptyArray(): void
    {
        $jsonManager = new LanguageFileManager('/path/to/nonexistent/file.json');
        $translations = $jsonManager->readJsonFile();
        $this->assertIsArray($translations);
        $this->assertEmpty($translations);
    }

    #[Test]
    public function readJsonFileHandlesInvalidJson(): void
    {
        // Create an invalid JSON content
        file_put_contents($this->tempFile, '{invalid json}');
        $jsonManager = new LanguageFileManager($this->tempFile);
        $result = $jsonManager->readJsonFile();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function addsNewTranslationsOverwritesExisting(): void
    {
        $jsonManager = new LanguageFileManager($this->tempFile);
        $additionalTranslations = ['new_key' => 'New translation'];
        $jsonManager->updateJsonFile($additionalTranslations);

        $content = json_decode(file_get_contents($this->tempFile), true, 512, JSON_THROW_ON_ERROR);

        $this->assertContains('New translation', $content);
        $this->assertNotContains('Welcome', $content);
    }
}
