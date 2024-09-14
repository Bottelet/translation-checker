<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Commands\CleanTranslation;
use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;


class CleanTranslationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->createTranslationFile('da', [
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
        $content = json_decode(file_get_contents($this->translationFile), true);
        $this->assertNotEmpty($content);
        $this->assertSame(['The title field is required for create' => 'Ice cream'], $content);
    }

    #[Test]
    public function itDoesNotUpdateWithPrintOption(): void
    {
        $this->artisan('translations:clean', [
            '--source' => 'da',
            '--print' => true
        ])->assertExitCode(0);
        $content = json_decode(file_get_contents($this->translationFile), true);
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
        $file = $this->createTranslationFile('ot', $initialTranslations);

        $this->artisan('translations:clean', [
            '--source' => 'ot',
        ])->assertExitCode(0);

        $content = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

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
        $file = $this->createTranslationFile('ot', $initialTranslations);

        $this->artisan('translations:clean', [
            '--source' => 'ot',
        ])->assertExitCode(0);

        $content = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEmpty($content);
    }
}
