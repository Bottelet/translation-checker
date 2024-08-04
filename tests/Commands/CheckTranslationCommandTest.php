<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Artisan;

class CheckTranslationCommandTest extends TestCase
{

    #[Test]
    public function itExecutesWithValidArgumentsAndOptions(): void
    {
        $translationFile = $this->tempDir.'/lang/fr.json';

        if ( ! file_exists(dirname($translationFile))) {
            mkdir(dirname($translationFile), 0777, true);
        }

        file_put_contents($translationFile, '{}');

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');

        $this->artisan('translations:check', [
            'target' => 'fr',
        ])->assertExitCode(0);

        $this->assertNotEmpty(json_decode(file_get_contents($translationFile)));
    }

    #[Test]
    public function itTranslatesMissing(): void
    {
        $translationFile = $this->tempDir.'/lang/fr.json';

        if ( ! file_exists(dirname($translationFile))) {
            mkdir(dirname($translationFile), 0777, true);
        }

        file_put_contents($translationFile, '{}');

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');

        $this->artisan('translations:check', [
            'target' => 'fr',
            '--source' => 'en',
            '--translate-missing' => true,
        ])->assertExitCode(0);

        foreach (json_decode(file_get_contents($translationFile), false) as $translation) {
            $this->assertEquals("nothing", $translation);
        }
    }

    #[Test]
    public function itSortsTranslationFileInAlphabeticOrder(): void
    {
        $translationFile = $this->tempDir.'/lang/fr.json';

        if (!file_exists(dirname($translationFile))) {
            mkdir(dirname($translationFile), 0777, true);
        }

        // Populate the translation file with unsorted content
        file_put_contents($translationFile, json_encode([
            'z' => 'Z value',
            'b' => 'B value',
            'a' => 'A value',
        ]));

        Config::set('translator.source_paths', []);
        Config::set('translator.language_folder', $this->tempDir.'/lang');

        // Execute the command with the sort option
        $this->artisan('translations:check', [
            'target' => 'fr',
            '--sort' => true,
        ])->assertExitCode(0);

        // Get the content of the sorted translation file
        $sortedContent = json_decode(file_get_contents($translationFile), true);

        // Assert that the translation file content is sorted alphabetically
        $expectedContent = [
            'a' => 'A value',
            'b' => 'B value',
            'z' => 'Z value',
        ];

        $this->assertSame($expectedContent, $sortedContent);
    }

}
