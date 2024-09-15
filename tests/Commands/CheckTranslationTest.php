<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Artisan;

class CheckTranslationTest extends TestCase
{
    private string $translationFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->tempDir.'/lang/fr.json';

        if ( ! file_exists(dirname($this->translationFile))) {
            mkdir(dirname($this->translationFile), 0777, true);
        }

        file_put_contents($this->translationFile, '');

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');
    }

    #[Test]
    public function itExecutesWithValidArgumentsAndOptions(): void
    {
        $this->artisan('translations:check', [
            'target' => 'fr',
        ])->assertExitCode(0);

        $this->assertNotEmpty(json_decode(file_get_contents($this->translationFile)));
    }

    #[Test]
    public function itTranslatesMissing(): void
    {
        $this->artisan('translations:check', [
            'target' => 'fr',
            '--source' => 'en',
            '--translate-missing' => true,
        ])->assertExitCode(0);

        foreach (json_decode(file_get_contents($this->translationFile), false) as $translation) {
            $this->assertEquals("nothing", $translation);
        }
    }

    #[Test]
    public function itSortsTranslationFileInAlphabeticOrder(): void
    {
        // Populate the translation file with unsorted content
        file_put_contents($this->translationFile, json_encode([
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

        $sortedContent = json_decode(file_get_contents($this->translationFile), true);

        $expectedContent = [
            'a' => 'A value',
            'b' => 'B value',
            'z' => 'Z value',
        ];

        $this->assertSame($expectedContent, $sortedContent);
    }

    #[Test]
    public function itOutputsSpecialCharactersCorrectly(): void
    {
        file_put_contents($this->translationFile, json_encode([
            'Welcome/Hi' => 'Velkommen/Hej',
            'PHP & Laravel ^^' => 'PHP & Laravel ^^',
        ], JSON_UNESCAPED_SLASHES));

        $this->artisan('translations:check', [
            'target' => 'da',
        ])->assertExitCode(0);

        $this->assertStringNotContainsString('Velkommen\/Hej', file_get_contents($this->translationFile));
        $this->assertStringContainsString('Welcome/Hi', file_get_contents($this->translationFile));
        $this->assertStringContainsString('Velkommen/Hej', file_get_contents($this->translationFile));
    }

}
