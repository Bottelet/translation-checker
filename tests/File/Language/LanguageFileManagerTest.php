<?php

namespace Bottelet\TranslationChecker\Tests\File\Language;

use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class LanguageFileManagerTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = $this->createJsonTranslationFile('da', [
            'welcome' => 'Welcome',
            'farewell' => 'Goodbye',
        ]);
    }

    #[Test]
    public function readJsonFileReturnsArray(): void
    {
        $jsonManager = new LanguageFileManagerFactory($this->tempFile);
        $translations = $jsonManager->readFile();
        $this->assertIsArray($translations);
        $this->assertCount(2, $translations);
    }

    #[Test]
    public function updateJsonFileUpdatesContentCorrectly(): void
    {
        $jsonManager = new LanguageFileManagerFactory($this->tempFile);
        $newTranslations = [
            'welcome' => 'Welcome to our application',
            'farewell' => 'See you soon',
            'greeting' => 'Hello, User',
        ];
        $jsonManager->updateFile($newTranslations);

        $updatedContent = json_decode(file_get_contents($this->tempFile), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($newTranslations, $updatedContent);
    }

    #[Test]
    public function ensureLastLineDoesNotAddComma(): void
    {
        $jsonManager = new LanguageFileManagerFactory($this->tempFile);
        $translations = [
            'welcome' => 'Welcome to our application',
            'farewell' => 'See you soon',
            'greeting' => 'Hello User',
        ];
        $jsonManager->updateFile($translations);
        $jsonContent = file_get_contents($this->tempFile);
        $lines = explode(PHP_EOL, $jsonContent);

        //remove last line as it's curly bracket
        array_pop($lines);
        $lastLine = end($lines);
        $this->assertStringNotContainsString(',', $lastLine);

        $jsonManager->updateFile(['new' => 'add new']);
        $this->assertStringNotContainsString(',', $lastLine);

        $jsonContent = file_get_contents($this->tempFile);
        $lines = explode(PHP_EOL, $jsonContent);
        array_pop($lines);
        $lastLine = end($lines);

        $this->assertStringNotContainsString(',', $lastLine);
        $this->assertStringContainsString('add new', $lastLine);
    }

    #[Test]
    public function nonexistentFileReadReturnsEmptyArray(): void
    {
        $jsonManager = new LanguageFileManagerFactory('/path/to/nonexistent/file.json');
        $translations = $jsonManager->readFile();
        $this->assertIsArray($translations);
        $this->assertEmpty($translations);
    }

    #[Test]
    public function readJsonFileThrowsExceptionOnInvalidJson(): void
    {
        // Create an invalid JSON content
        file_put_contents($this->tempFile, '{invalid json}');
        $jsonManager = new LanguageFileManagerFactory($this->tempFile);
        $this->expectException(RuntimeException::class);
        $jsonManager->readFile();
    }

    #[Test]
    public function addsNewTranslationsOverwritesExisting(): void
    {
        $jsonManager = new LanguageFileManagerFactory($this->tempFile);
        $additionalTranslations = ['new_key' => 'New translation'];
        $jsonManager->updateFile($additionalTranslations);

        $content = json_decode(file_get_contents($this->tempFile), true, 512, JSON_THROW_ON_ERROR);

        $this->assertContains('New translation', $content);
        $this->assertNotContains('Welcome', $content);
    }

    #[Test]
    public function updateSpecialCharactersCorrectly(): void
    {
        $jsonManager = new LanguageFileManagerFactory($this->tempFile);
        $newTranslations = [
            'Welcome/Hi' => 'Velkommen/Hej',
            'PHP & Laravel ^^' => 'PHP & Laravel ^^',
            'This should work / Using $ Special chars Is okay £ for æ ø å o' => 'This should work / Using $ Special chars Is okay £ for unicode æ ø å ó',
            'A very long key with special characters / * & ^ % $ @ # ! + - = | ? < > , . : ; { } [ ] ( ) _ € £ € æ ø å o' => 'A very long key with special characters / * & ^ % $ @ # ! + - = | ? < > , . : ; { } [ ] ( ) _ € £ € æ ø å ó',
        ];
        $jsonManager->updateFile($newTranslations);
        $updatedContent = file_get_contents($this->tempFile);

        $this->assertStringNotContainsString('Velkommen\/Hej', $updatedContent);
        $this->assertStringContainsString('Welcome/Hi', $updatedContent);
        $this->assertStringContainsString('PHP & Laravel ^^', $updatedContent);
        $this->assertStringContainsString('This should work / Using $ Special chars Is okay £ for unicode æ ø å ó', $updatedContent);
        $this->assertStringContainsString('A very long key with special characters / * & ^ % $ @ # ! + - = | ? < > , . : ; { } [ ] ( ) _ € £ € æ ø å ó', $updatedContent);
    }
}
