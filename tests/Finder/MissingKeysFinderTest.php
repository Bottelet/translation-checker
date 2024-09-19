<?php

namespace Bottelet\TranslationChecker\Tests\Finder;

use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class MissingKeysFinderTest extends TestCase
{
    #[Test]
    public function findTranslatableStringsFindsStringsWithDollarT(): void
    {
        $translationFinder = new MissingKeysFinder;

        $foundStrings = $translationFinder->findTranslatableStrings([$this->vueFile]);
        $this->assertContains('welcome_message', $foundStrings);
    }

    #[Test]
    public function findTranslatableStringsChecksNonPhpFiles(): void
    {
        $translationFinder = new MissingKeysFinder;
        $nonPhpFile = $this->createTempFile('nonPhpContent.html', file_get_contents($this->noTranslationsBladeFile));
        $foundStrings = $translationFinder->findTranslatableStrings([$nonPhpFile]);
        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function findTranslatableStringsHandlesEmptyFiles(): void
    {
        $emptyFile = $this->createTempFile('empty.php', '');
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$emptyFile]);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function nonexistentPathShouldJustBeSkipped(): void
    {
        $translationFinder = new MissingKeysFinder;
        $nonexistentFile = new SplFileInfo('/nonexistent/path/file.php');

        $foundStrings = $translationFinder->findTranslatableStrings([$nonexistentFile]);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function pathNormalization(): void
    {
        $normalizedFile = $this->createTempFile('testFile.php', "<?php echo __('normalized string');");

        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$normalizedFile]);

        $this->assertNotEmpty($foundStrings);
        $this->assertContains('normalized string', $foundStrings);
    }

    #[Test]
    public function throwExceptionFilesWithSyntaxErrors(): void
    {
        $fileWithSyntaxError = $this->createTempFile('syntaxError.php', "<?php echo __('missing_semicolon'\")");
        $this->expectException(\Exception::class);
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$fileWithSyntaxError]);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function handlesFilesWithMultipleTranslationFunctions(): void
    {
        $multiFunctionFile = $this->createTempFile('multiFunction.php', "<?php echo __('first_key'); echo __('second_key');");
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$multiFunctionFile]);

        $this->assertContains('first_key', $foundStrings);
        $this->assertContains('second_key', $foundStrings);
    }

    #[Test]
    public function correctlyIdentifiesTranslationKeysWithVariables(): void
    {
        $fileWithVariables = $this->createTempFile('variableKey.php', "<?php echo __('key_with_variable', ['name' => \$name]); echo __('a text with :key inside string', ['key' => \$name]);");
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$fileWithVariables]);

        $this->assertEquals(['key_with_variable', 'a text with :key inside string'], $foundStrings);
    }

    #[Test]
    public function canFindFunctionsInController(): void
    {
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$this->phpControllerFile]);
        $this->assertCount(10, $foundStrings);
    }

    #[Test]
    public function distinguishesBetweenTranslationAndOtherScriptingFunctions(): void
    {
        $jsFile = $this->createTempFile('script.js', "console.log('Not a PHP file');");
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$jsFile]);

        // Assuming TranslationFinder is expected to ignore non-PHP files
        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function findsPersistentKeysFromConfig(): void
    {
        $this->app['config']->set('translator.persistent_keys', ['persistent_key', 'A sentence that should be added to the translation file']);
        $multiFunctionFile = $this->createTempFile('multiFunction.php', "<?php echo __('first_key');");
        $translationFinder = new MissingKeysFinder;
        $foundStrings = $translationFinder->findTranslatableStrings([$multiFunctionFile]);

        $this->assertContains('first_key', $foundStrings);
        $this->assertContains('persistent_key', $foundStrings);
        $this->assertContains('A sentence that should be added to the translation file', $foundStrings);
    }

    #[Test]
    public function findMissingTranslationsFindsPersistentKeysFromConfig(): void
    {
        $this->app['config']->set('translator.persistent_keys', ['persistent_key', 'A sentence that should be added to the translation file']);

        $multiFunctionFile = $this->createTempFile('multiFunction.php', "<?php echo __('first_key');");
        $translationFinder = new MissingKeysFinder;

        $foundStrings = $translationFinder->findMissingTranslatableStrings([$multiFunctionFile], ['first_key' => 'translated', 'persistent_key' => 'translated']);
        $this->assertArrayHasKey('A sentence that should be added to the translation file', $foundStrings);
    }


    #[Test]
    public function findMissingTranslatableStringUseKeyAsDefaultValue(): void
    {
        $multiFunctionFile = $this->createTempFile('multiFunction.php', "<?php echo __('da.key.test'); __('a long string');");
        $translationFinder = new MissingKeysFinder;

        $foundStrings = $translationFinder->findMissingTranslatableStrings([$multiFunctionFile], []);
        $this->assertSame(['da.key.test' => 'da.key.test', 'a long string' => 'a long string'], $foundStrings);

    }
}
