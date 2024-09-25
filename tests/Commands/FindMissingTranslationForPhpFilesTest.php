<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class FindMissingTranslationForPhpFilesTest extends TestCase
{
    private string $translationFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->tempDir.'/lang/da.php';

        if (! file_exists(dirname($this->translationFile))) {
            mkdir(dirname($this->translationFile), 0777, true);
        }

        file_put_contents($this->translationFile, '<?php return [];');

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');
    }

    #[Test]
    public function itFindsMissingAndAddsToFileWithoutFlag(): void
    {
        $this->artisan('translations:find-missing', [
            '--source' => 'da',
        ])->assertExitCode(0);

        $this->assertNotEmpty(require $this->translationFile);
    }

    #[Test]
    public function itPrintsMissingAndDoesNotAddToFileWithFlag(): void
    {
        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');

        $this->artisan('translations:find-missing', [
            '--source' => 'da',
            '--print' => true,
        ])->assertExitCode(0);

        $content = require $this->translationFile;
        $this->assertIsArray($content);
        $this->assertEmpty($content);
    }

    #[Test]
    public function throwExceptionIfConifgNotArray()
    {
        Config::set('translator.source_paths', $this->tempDir);

        $this->expectException(RuntimeException::class);
        $this->artisan('translations:find-missing', [
            '--source' => 'da',
            '--print' => true,
        ])->assertExitCode(0);
    }
}
