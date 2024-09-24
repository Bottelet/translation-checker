<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class CleanTranslationForPhpFilesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->createPhpTranslationFile('da.php', [
            'sundae' => 'sundae',
            'softice' => 'softice',
            'cubes' => 'cubes',
            'The title field is required for create' => 'Ice cream',
        ]);

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');
    }

    #[Test]
    public function itFindsUnusedTranslationsAndRemoveThem(): void
    {
        $this->artisan('translations:clean', [
            '--source' => 'da',
        ])->assertExitCode(0);
        $content = require $this->translationFile;
        $this->assertNotEmpty($content);
        $this->assertSame(['The title field is required for create' => 'Ice cream'], $content);
    }

    #[Test]
    public function itDoesNotUpdateWithPrintOption(): void
    {
        $this->artisan('translations:clean', [
            '--source' => 'da',
            '--print' => true,
        ])->assertExitCode(0);
        $content = require $this->translationFile;
        $this->assertNotEmpty($content);
        $this->assertSame([
            'sundae' => 'sundae',
            'softice' => 'softice',
            'cubes' => 'cubes',
            'The title field is required for create' => 'Ice cream',
        ], $content);
    }

    #[Test]
    public function itCorrectlyCleansTranslationsForOtherLanguages(): void
    {
        $initialTranslations = [
            'this does not exists' => "vaj ghu'vam taHbe'",
            'The title field is required for create' => "Vaj che'meH mIw'a' lughovlaH",
            'unused key' => 'voq',
        ];
        $file = $this->createPhpTranslationFile('ot.php', $initialTranslations);


        $this->artisan('translations:clean', [
            '--source' => 'ot',
        ])->assertExitCode(0);

        $content = require $file;
        $this->assertSame([
            'The title field is required for create' => "Vaj che'meH mIw'a' lughovlaH",
        ], $content);
        $this->assertArrayNotHasKey('sundae', $content);
        $this->assertArrayNotHasKey('unused_key', $content);
    }

    #[Test]
    public function itCorrectlyHandlesCaseInsensitiveKeys(): void
    {
        $initialTranslations = [
            'the title field is required for create' => 'Ice cream',
            'you are currently not logged in.' => 'You are currently not logged in.',
            'Please_log_in' => 'please_log_in',
        ];
        $file = $this->createPhpTranslationFile('ot.php', $initialTranslations);

        $this->artisan('translations:clean', [
            '--source' => 'ot',
        ])->assertExitCode(0);

        $content = require $file;
        $this->assertEmpty($content);
    }
}