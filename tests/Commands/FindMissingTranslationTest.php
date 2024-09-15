<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class FindMissingTranslationTest extends TestCase
{
    private string $translationFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->tempDir.'/lang/da.json';

        if (! file_exists(dirname($this->translationFile))) {
            mkdir(dirname($this->translationFile), 0777, true);
        }

        file_put_contents($this->translationFile, '');

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');
    }

    #[Test]
    public function itFindsMissingAndAddsToFileWithoutFlag(): void
    {
        $this->artisan('translations:find-missing', [
            '--source' => 'da',
        ])->assertExitCode(0);

        $this->assertNotEmpty(json_decode(file_get_contents($this->translationFile)));
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

        $this->assertEmpty(json_decode(file_get_contents($this->translationFile)));
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
