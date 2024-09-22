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

class TranslationManagerTest extends TestCase
{
    protected string $testDir;
    protected string $jsonFilePath;
    protected TranslationManager $translationManager;
    protected MockObject $translationServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = sys_get_temp_dir().'/translation_checker_tests';
        if (! file_exists($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }

        file_put_contents($this->testDir.'/file1.php', "<?php echo __('Hello, World!');");
        file_put_contents($this->testDir.'/file2.vue', "<template> {{ \$t(\"Something cool \") }}('</template>");
        file_put_contents($this->testDir.'/file3.jsx', "return (
            <div>
              <h1>{t('greeting')}</h1>
            </div>
          );");
        file_put_contents($this->testDir.'/file4.svelte', "{\$_('app.title')}");

        $this->jsonFilePath = $this->testDir.'/translations/en.json';
        if (! file_exists(dirname($this->jsonFilePath))) {
            mkdir(dirname($this->jsonFilePath), 0777, true);
        }
        file_put_contents($this->jsonFilePath, '{"Hello, World!": "Already Translated Hello, World!"}');

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
    public function translationsAreAppendedWhenNotUsingTranslationService(): void
    {
        $this->translationServiceMock->expects($this->never())
                                     ->method('translateBatch');

        $missingTranslations = $this->translationManager->updateTranslationsFromFile(
            [$this->testDir],
            $this->jsonFilePath
        );
        $this->assertEquals([
            'app.title'       => null,
            'Something cool ' => null,
            'greeting'        => null,
        ], $missingTranslations);
    }

    #[Test]
    public function updateTranslationsFromFileTranslateMissing(): void
    {
        $this->translationServiceMock->method('isConfigured')->willReturn(true);
        $translations = [
            'Hello, World!'   => 'Translated Hello, World!',
            'Something cool ' => 'Translated Something cool!',
            'greeting'        => 'Translated greeting!',
            'app.title'       => 'Translated app.title!',
        ];

        $this->translationServiceMock
            ->method('translateBatch')
            ->willReturnCallback(function ($keys) use ($translations) {
                $translatedTexts = [];
                foreach ($keys as $key) {
                    $translatedTexts[$key] = $translations[$key] ?? 'Another Translation';
                }

                return $translatedTexts;
            });

        $missingTranslations = $this->translationManager->updateTranslationsFromFile(
            [$this->testDir],
            $this->jsonFilePath,
            false,
            'en',
            true
        );

        unset($translations['Hello, World!']);

        $this->assertEquals($translations, $missingTranslations);
        $this->assertNotContains('Hello, World!', array_keys($missingTranslations));

        $jsonContent = file_get_contents($this->jsonFilePath);

        foreach ($translations as $key => $translation) {
            $this->assertStringContainsString("\"{$key}\": \"{$translation}\"", $jsonContent);
        }
        $this->assertStringContainsString('Already Translated Hello, World!', $jsonContent);
    }

    #[Test]
    public function translationNotPerformedWithEmptySourceFilePath(): void
    {
        $missingTranslations = $this->translationManager->updateTranslationsFromFile([], $this->jsonFilePath);

        $this->assertEquals([], $missingTranslations);

        $jsonContent = file_get_contents($this->jsonFilePath);
        $this->assertStringContainsString('Already Translated Hello, World!', $jsonContent);
    }

    #[Test]
    public function translationPerformedWithEmptyJsonFile(): void
    {
        $this->translationServiceMock->method('isConfigured')->willReturn(true);
        file_put_contents($this->jsonFilePath, '{}');

        $translations = [
            'Hello, World!'   => 'Translated Hello, World!',
            'Something cool ' => 'Translated Something cool!',
            'greeting'        => 'Translated greeting!',
            'app.title'       => 'Translated app.title!',
        ];

        $this->translationServiceMock
            ->method('translateBatch')
            ->willReturnCallback(function ($keys) use ($translations) {
                $translatedTexts = [];
                foreach ($keys as $key) {
                    $translatedTexts[$key] = $translations[$key] ?? 'Another Translation';
                }

                return $translatedTexts;
            });

        $missingTranslations = $this->translationManager->updateTranslationsFromFile(
            [$this->testDir],
            $this->jsonFilePath,
            false,
            'en',
            true
        );
        $this->assertEquals($translations, $missingTranslations);

        $jsonContent = file_get_contents($this->jsonFilePath);
        foreach ($translations as $key => $translation) {
            $this->assertStringContainsString("\"{$key}\": \"{$translation}\"", $jsonContent);
        }
    }

    #[Test]
    public function throwsExceptionWhenTranslationServiceIsNotSet(): void
    {
        $this->expectException(TranslationServiceException::class);

        $this->translationServiceMock = $this->createMock(GoogleTranslator::class);
        $this->translationManager     = new TranslationManager(
            new AlphabeticSort,
            $this->translationServiceMock
        );

        $this->translationManager->updateTranslationsFromFile(
            [$this->testDir],
            $this->jsonFilePath,
            false,
            'en',
            true
        );
    }
}
