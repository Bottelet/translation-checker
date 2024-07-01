<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\TranslationFinder;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class TranslationFinderTest extends TestCase
{
    private string $tempDir;
    private SplFileInfo $vueFile;
    private SplFileInfo $phpController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = '/translation_checker_tests';
        if (! file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $vueFilePath = $this->tempDir . '/test.vue';
        $phpController = $this->tempDir . '/TestController.php';

        file_put_contents($phpController, file_get_contents('translation-checker/tests/templates/TestController.php'));
        file_put_contents($vueFilePath, file_get_contents('translation-checker/tests/templates/dollar-t.vue'));

        $this->vueFile = new SplFileInfo($vueFilePath);
        $this->phpController = new SplFileInfo($phpController);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->tempDir}/*.*"));
        rmdir($this->tempDir);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function findTranslatableStringsFindsStringsWithDollarT(): void
    {
        $translationFinder = new TranslationFinder;

        $foundStrings = $translationFinder->findTranslatableStrings([$this->vueFile]);
        $this->assertContains('welcome_message', $foundStrings['all']);
    }

    /**
     * @test
     */
    public function findTranslatableStringsIgnoresNonPhpFiles(): void
    {
        $translationFinder = new TranslationFinder;
        $nonPhpFile = new SplFileInfo($this->tempDir . '/nonPhpContent.html');

        file_put_contents($this->tempDir . '/nonPhpContent.php', file_get_contents('translation-checker/tests/templates/no-translations.blade.php'));

        $foundStrings = $translationFinder->findTranslatableStrings([$nonPhpFile]);

        $this->assertEmpty($foundStrings['all']);
    }

    /**
     * @test
     */
    public function findTranslatableStringsHandlesEmptyFiles(): void
    {
        $emptyFile = new SplFileInfo($this->tempDir . '/empty.php');
        file_put_contents($this->tempDir . '/empty.php', '');

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$emptyFile]);

        $this->assertEmpty($foundStrings['all']);
    }

    /**
     * @test
     */
    public function nonexistentPathShouldJustBeSkipped(): void
    {
        $translationFinder = new TranslationFinder;
        $nonexistentFile = new SplFileInfo('/nonexistent/path/file.php');

        $foundStrings = $translationFinder->findTranslatableStrings([$nonexistentFile]);

        $this->assertEmpty($foundStrings['all']);
    }

    /**
     * @test
     */
    public function pathNormalization(): void
    {
        $normalizedFile = new SplFileInfo($this->tempDir . '/testFile.php');
        file_put_contents($this->tempDir . '/testFile.php', "<?php echo __('normalized string');");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$normalizedFile]);

        $this->assertNotEmpty($foundStrings);
        $this->assertContains('normalized string', $foundStrings['all']);
    }

    /**
     * @test
     */
    public function ignoresFilesWithSyntaxErrors(): void
    {
        $fileWithSyntaxError = $this->tempDir . '/syntaxError.php';
        file_put_contents($fileWithSyntaxError, "<?php echo __('missing_semicolon'");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($fileWithSyntaxError)]);

        $this->assertEmpty($foundStrings['all']);
    }

    /**
     * @test
     */
    public function handlesFilesWithMultipleTranslationFunctions(): void
    {
        $multiFunctionFile = $this->tempDir . '/multiFunction.php';
        file_put_contents($multiFunctionFile, "<?php echo __('first_key'); echo __('second_key');");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($multiFunctionFile)]);

        $this->assertContains('first_key', $foundStrings['all']);
        $this->assertContains('second_key', $foundStrings['all']);
    }

    /**
     * @test
     */
    public function correctlyIdentifiesTranslationKeysWithVariables(): void
    {
        $fileWithVariables = $this->tempDir . '/variableKey.php';
        file_put_contents($fileWithVariables, "<?php echo __('key_with_variable', ['name' => \$name]); echo __('a text with :key inside string', ['key' => \$name]);");

        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([new SplFileInfo($fileWithVariables)]);

        $this->assertEquals(['key_with_variable', 'a text with :key inside string'], $foundStrings['all']);
    }

    /**
     * @test
     */
    public function canFindFunctionsInController(): void
    {
        $translationFinder = new TranslationFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$this->phpController]);

        $this->assertCount(10, $foundStrings['all']);
    }

    /**
     * @test
     */
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
