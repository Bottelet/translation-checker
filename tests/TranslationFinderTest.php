<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\TranslationFinder;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class TranslationFinderTest extends TestCase
{
    #[Test]
    public function findTranslatableStringsFindsStringsWithDollarT(): void
    {
        $translationFinder = new TranslationFinder;

        $foundStrings = $translationFinder->findTranslatableStrings([$this->vueFile]);
        $this->assertContains('welcome_message', $foundStrings['all']);
    }

    #[Test]
    public function findTranslatableStringsChecksNonPhpFiles(): void
    {
        $translationFinder = new TranslationFinder;
        $nonPhpFile = new SplFileInfo($this->tempDir . '/nonPhpContent.html');

        file_put_contents($this->tempDir . '/nonPhpContent.html', file_get_contents($this->noTranslationsBladeFile));
        $foundStrings = $translationFinder->findTranslatableStrings([$nonPhpFile]);
        $this->assertEmpty($foundStrings['all']);
    }

    #[Test]
    public function findTranslatableStringsHandlesEmptyFiles(): void
    {
        $emptyFile = new SplFileInfo($this->tempDir . '/empty.php');
        file_put_contents($this->tempDir . '/empty.php', '');

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$emptyFile]);

        $this->assertEmpty($foundStrings['all']);
    }

    #[Test]
    public function nonexistentPathShouldJustBeSkipped(): void
    {
        $translationFinder = new TranslationFinder;
        $nonexistentFile = new SplFileInfo('/nonexistent/path/file.php');

        $foundStrings = $translationFinder->findTranslatableStrings([$nonexistentFile]);

        $this->assertEmpty($foundStrings['all']);
    }

    #[Test]
    public function pathNormalization(): void
    {
        $normalizedFile = new SplFileInfo($this->tempDir . '/testFile.php');
        file_put_contents($this->tempDir . '/testFile.php', "<?php echo __('normalized string');");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$normalizedFile]);

        $this->assertNotEmpty($foundStrings);
        $this->assertContains('normalized string', $foundStrings['all']);
    }

    #[Test]
    public function throwExceptionFilesWithSyntaxErrors(): void
    {
        $fileWithSyntaxError = $this->tempDir . '/syntaxError.php';
        file_put_contents($fileWithSyntaxError, "<?php echo __('missing_semicolon'");

        $this->expectException(\Exception::class);
        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($fileWithSyntaxError)]);

        $this->assertEmpty($foundStrings['all']);
    }

    #[Test]
    public function handlesFilesWithMultipleTranslationFunctions(): void
    {
        $multiFunctionFile = $this->tempDir . '/multiFunction.php';
        file_put_contents($multiFunctionFile, "<?php echo __('first_key'); echo __('second_key');");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($multiFunctionFile)]);

        $this->assertContains('first_key', $foundStrings['all']);
        $this->assertContains('second_key', $foundStrings['all']);
    }

    #[Test]
    public function correctlyIdentifiesTranslationKeysWithVariables(): void
    {
        $fileWithVariables = $this->tempDir . '/variableKey.php';
        file_put_contents($fileWithVariables, "<?php echo __('key_with_variable', ['name' => \$name]); echo __('a text with :key inside string', ['key' => \$name]);");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($fileWithVariables)]);

        $this->assertEquals(['key_with_variable', 'a text with :key inside string'], $foundStrings['all']);
    }

    #[Test]
    public function canFindFunctionsInController(): void
    {
        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$this->phpControllerFile]);

        $this->assertCount(10, $foundStrings['all']);
    }

    #[Test]
    public function distinguishesBetweenTranslationAndOtherScriptingFunctions(): void
    {
        $jsFile = $this->tempDir . '/script.js';
        file_put_contents($jsFile, "console.log('Not a PHP file');");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($jsFile)]);

        // Assuming TranslationFinder is expected to ignore non-PHP files
        $this->assertEmpty($foundStrings['all']);
    }
}
