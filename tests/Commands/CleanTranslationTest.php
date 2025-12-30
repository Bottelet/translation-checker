<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class CleanTranslationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->createJsonTranslationFile('da', [
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
            '--print' => true,
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
        $file = $this->createJsonTranslationFile('ot', $initialTranslations);

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
        $file = $this->createJsonTranslationFile('ot', $initialTranslations);

        $this->artisan('translations:clean', [
            '--source' => 'ot',
        ])->assertExitCode(0);

        $content = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEmpty($content);
    }

    #[Test]
    public function itCleansAllLanguageFilesWithAllFlag(): void
    {
        $secondFile = $this->createJsonTranslationFile('de', [
            'sundae' => 'Eisbecher',
            'The title field is required for create' => 'Das Titelfeld ist erforderlich',
        ]);

        $this->artisan('translations:clean', [
            '--all' => true,
        ])->assertExitCode(0);

        $daContent = json_decode(file_get_contents($this->translationFile), true);
        $deContent = json_decode(file_get_contents($secondFile), true);

        $this->assertSame(['The title field is required for create' => 'Ice cream'], $daContent);
        $this->assertSame(['The title field is required for create' => 'Das Titelfeld ist erforderlich'], $deContent);
    }

    #[Test]
    public function itCleansAllLanguageFilesWhenNoSourceSpecified(): void
    {
        $secondFile = $this->createJsonTranslationFile('fr', [
            'cubes' => 'cubes',
            'The title field is required for create' => 'Le champ titre est requis',
        ]);

        $this->artisan('translations:clean')->assertExitCode(0);

        $daContent = json_decode(file_get_contents($this->translationFile), true);
        $frContent = json_decode(file_get_contents($secondFile), true);

        $this->assertSame(['The title field is required for create' => 'Ice cream'], $daContent);
        $this->assertSame(['The title field is required for create' => 'Le champ titre est requis'], $frContent);
    }

    #[Test]
    public function itCleansOnlySpecifiedSourceWhenProvided(): void
    {
        $secondFile = $this->createJsonTranslationFile('es', [
            'sundae' => 'sundae',
            'The title field is required for create' => 'El campo de título es obligatorio',
        ]);

        $this->artisan('translations:clean', [
            '--source' => 'da',
        ])->assertExitCode(0);

        $daContent = json_decode(file_get_contents($this->translationFile), true);
        $esContent = json_decode(file_get_contents($secondFile), true);

        $this->assertSame(['The title field is required for create' => 'Ice cream'], $daContent);

        $this->assertSame([
            'sundae' => 'sundae',
            'The title field is required for create' => 'El campo de título es obligatorio',
        ], $esContent);
    }

    #[Test]
    public function itUsesAppLocaleAsDefaultSourceWhenSpecified(): void
    {
        Config::set('app.locale', 'da');

        $this->artisan('translations:clean', [
            '--source' => 'da',
        ])->assertExitCode(0);

        $content = json_decode(file_get_contents($this->translationFile), true);
        $this->assertSame(['The title field is required for create' => 'Ice cream'], $content);
    }
}
