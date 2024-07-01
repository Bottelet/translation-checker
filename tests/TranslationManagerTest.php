<?php

use Bottelet\TranslationChecker\FileManagement;
use Bottelet\TranslationChecker\JsonTranslationFileManager;
use Bottelet\TranslationChecker\TranslationFinder;
use Bottelet\TranslationChecker\TranslationManager;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslationManagerTest extends TestCase
{
    protected string $testDir;
    protected string $jsonFilePath;
    protected TranslationManager $translationManager;
    protected MockObject $translationServiceMock;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = sys_get_temp_dir() . '/translation_checker_tests';
        if (! file_exists($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }

        file_put_contents($this->testDir . '/file1.php', "<?php echo __('Hello, World!');");
        file_put_contents($this->testDir . '/file2.vue', "<template> {{ \$t(\"Something cool \") }}('</template>");
        file_put_contents($this->testDir . '/file3.jsx', "return (
            <div>
              <h1>{t('greeting')}</h1>
            </div>
          );");
        file_put_contents($this->testDir . '/file4.svelte', "{\$_('app.title')}");

        $this->jsonFilePath = $this->testDir . '/translations/en.json';
        if (! file_exists(dirname($this->jsonFilePath))) {
            mkdir(dirname($this->jsonFilePath), 0777, true);
        }
        file_put_contents($this->jsonFilePath, '{"Hello, World!": "Already Translated Hello, World!"}');

        $this->translationServiceMock = $this->createMock(GoogleTranslator::class);
        $this->translationManager = new TranslationManager(
            new FileManagement,
            new TranslationFinder,
            new JsonTranslationFileManager,
            $this->translationServiceMock
        );
    }

    protected function tearDown(): void
    {
        // Recursively remove the testing directory
        $it = new RecursiveDirectoryIterator($this->testDir, FilesystemIterator::SKIP_DOTS);
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

    /**
     * @test
     */
    public function translationsAreAppendedWhenNotUsingTranslationService(): void
    {
        $this->translationServiceMock->expects($this->never())
            ->method('translateBatch');

        $missingTranslations = $this->translationManager->updateTranslationsFromFile([$this->testDir], $this->jsonFilePath);
        $this->assertEquals([
            'app.title' => '',
            'Something cool ' => '',
            'greeting' => '',
        ], $missingTranslations);
    }

    /**
     * @test
     */
    public function updateTranslationsFromFile_TranslateMissing(): void
    {
        $translations = [
            'Hello, World!' => 'Translated Hello, World!',
            'Something cool ' => 'Translated Something cool!',
            'greeting' => 'Translated greeting!',
            'app.title' => 'Translated app.title!',
        ];

        $this->translationServiceMock
            ->method('translateBatch')
            ->willReturnCallback(function ($keys) use ($translations) {
                $translatedTexts = [];
                foreach ($keys as $key) {
                    $translatedTexts[] = $translations[$key] ?? 'Another Translation';
                }

                return $translatedTexts;
            });

        $missingTranslations = $this->translationManager->updateTranslationsFromFile([$this->testDir], $this->jsonFilePath, 'en', true);

        unset($translations['Hello, World!']);
        $this->assertEquals($translations, $missingTranslations);
        $this->assertNotContains('Hello, World!', array_keys($missingTranslations));

        $jsonContent = file_get_contents($this->jsonFilePath);
        foreach ($translations as $key => $translation) {
            $this->assertStringContainsString("\"{$key}\": \"{$translation}\"", $jsonContent);
        }
        $this->assertStringContainsString('Already Translated Hello, World!', $jsonContent);
    }

    /**
     * @test
     */
    public function translationNotPerformedWithEmptySourceFilePath(): void
    {
        $missingTranslations = $this->translationManager->updateTranslationsFromFile([], $this->jsonFilePath);

        $this->assertEquals([], $missingTranslations);

        $jsonContent = file_get_contents($this->jsonFilePath);
        $this->assertStringContainsString('Already Translated Hello, World!', $jsonContent);
    }

    /**
     * @test
     */
    public function translationPerformedWithEmptyJsonFile(): void
    {
        file_put_contents($this->jsonFilePath, '');

        $translations = [
            'Hello, World!' => 'Translated Hello, World!',
            'Something cool ' => 'Translated Something cool!',
            'greeting' => 'Translated greeting!',
            'app.title' => 'Translated app.title!',
        ];

        $this->translationServiceMock
            ->method('translateBatch')
            ->willReturnCallback(function ($keys) use ($translations) {
                $translatedTexts = [];
                foreach ($keys as $key) {
                    $translatedTexts[] = $translations[$key] ?? 'Another Translation';
                }

                return $translatedTexts;
            });

        $missingTranslations = $this->translationManager->updateTranslationsFromFile([$this->testDir], $this->jsonFilePath, 'en', true);
        $this->assertEquals($translations, $missingTranslations);

        $jsonContent = file_get_contents($this->jsonFilePath);
        foreach ($translations as $key => $translation) {
            $this->assertStringContainsString("\"{$key}\": \"{$translation}\"", $jsonContent);
        }
    }
}
