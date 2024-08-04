<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\LanguageFileManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonTranslationFileManagerTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary JSON file for testing
        $this->tempFile = tempnam(sys_get_temp_dir(), 'trans');
        file_put_contents($this->tempFile, json_encode([
            'welcome' => 'Welcome',
            'farewell' => 'Goodbye',
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        // Remove the temporary JSON file
        unlink($this->tempFile);

        parent::tearDown();
    }

    #[Test]
    public function readJsonFileReturnsArray(): void
    {
        $jsonManager = new LanguageFileManager;
        $translations = $jsonManager->readJsonFile($this->tempFile);
        $this->assertIsArray($translations);
        $this->assertCount(2, $translations);
    }

    #[Test]
    public function updateJsonFileUpdatesContentCorrectly(): void
    {
        $jsonManager = new LanguageFileManager;
        $newTranslations = [
            'welcome' => 'Welcome to our application',
            'farewell' => 'See you soon',
            'greeting' => 'Hello, User',
        ];
        $jsonManager->updateJsonFile($this->tempFile, $newTranslations);

        $updatedContent = json_decode(file_get_contents($this->tempFile), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($newTranslations, $updatedContent);
    }

    #[Test]
    public function ensureLastLineDoesNotAddComma(): void
    {
        $jsonManager = new LanguageFileManager;
        $translations = [
            'welcome' => 'Welcome to our application',
            'farewell' => 'See you soon',
            'greeting' => 'Hello User',
        ];
        $jsonManager->updateJsonFile($this->tempFile, $translations);
        $jsonContent = file_get_contents($this->tempFile);
        $lines = explode(PHP_EOL, $jsonContent);
        //remove last line as it's curly bracket
        array_pop($lines);
        $lastLine = end($lines);
        $this->assertStringNotContainsString(',', $lastLine);

        $jsonManager->updateJsonFile($this->tempFile, ['new' => 'add new']);
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
        $jsonManager = new LanguageFileManager;
        $translations = [
            'b' => 'Second',
            'a' => 'First',
        ];
        $jsonManager->updateJsonFile($this->tempFile, $translations);
        $jsonManager->sortJsonFile($this->tempFile);

        $sortedContent = json_decode(file_get_contents($this->tempFile), true);
        $expectedOrder = ['a' => 'First', 'b' => 'Second'];
        $this->assertEquals(array_keys($expectedOrder), array_keys($sortedContent));
    }

    #[Test]
    public function nonexistentFileReadReturnsEmptyArray(): void
    {
        $jsonManager = new LanguageFileManager;
        $translations = $jsonManager->readJsonFile('/path/to/nonexistent/file.json');
        $this->assertIsArray($translations);
        $this->assertEmpty($translations);
    }

    #[Test]
    public function readJsonFileHandlesInvalidJson(): void
    {
        // Create an invalid JSON content
        file_put_contents($this->tempFile, '{invalid json}');
        $jsonManager = new LanguageFileManager;
        $result = $jsonManager->readJsonFile($this->tempFile);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function addsNewTranslationsOverwritesExisting(): void
    {
        $jsonManager = new LanguageFileManager;
        $additionalTranslations = ['new_key' => 'New translation'];
        $jsonManager->updateJsonFile($this->tempFile, $additionalTranslations);

        $content = json_decode(file_get_contents($this->tempFile), true, 512, JSON_THROW_ON_ERROR);

        $this->assertContains('New translation', $content);
        $this->assertNotContains('Welcome', $content);
    }
}
