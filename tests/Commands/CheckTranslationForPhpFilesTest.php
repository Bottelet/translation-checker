<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class CheckTranslationForPhpFilesTest extends TestCase
{
    private string $translationFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->tempDir.'/lang/fr.php';

        if (! file_exists(dirname($this->translationFile))) {
            mkdir(dirname($this->translationFile), 0777, true);
        }

        file_put_contents($this->translationFile, '<?php return [];');

        Config::set('translator.source_paths', [$this->tempDir]);
        Config::set('translator.language_folder', $this->tempDir.'/lang');
    }

    #[Test]
    public function itExecutesWithValidArgumentsAndOptions(): void
    {
        $this->artisan('translations:check', [
            'target' => 'fr',
        ])->assertExitCode(0);

        $this->assertNotEmpty(require $this->translationFile);
    }

    #[Test]
    public function itTranslatesMissing(): void
    {
        $this->artisan('translations:check', [
            'target' => 'fr',
            '--source' => 'en',
            '--translate-missing' => true,
        ])->assertExitCode(0);


        $file = require $this->translationFile;

        foreach ($file as $translation) {
            $this->assertEquals('nothing', $translation);
        }
    }

    #[Test]
    public function itSortsTranslationFileInAlphabeticOrder(): void
    {
        // Populate the translation file with unsorted content
        file_put_contents($this->translationFile, '<?php return ['."\n".
            '    \'z\' => \'Z value\','."\n".
            '    \'b\' => \'B value\','."\n".
            '    \'a\' => \'A value\','."\n".
            '];');

        Config::set('translator.source_paths', []);
        Config::set('translator.language_folder', $this->tempDir.'/lang');

        // Execute the command with the sort option
        $this->artisan('translations:check', [
            'target' => 'fr',
            '--sort' => true,
        ])->assertExitCode(0);

        $sortedContent = require $this->translationFile;

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
        file_put_contents($this->translationFile, '<?php return ['."\n".
            '    \'Welcome/Hi\' => \'Velkommen/Hej\','."\n".
            '    \'PHP & Laravel ^^\' => \'PHP & Laravel ^^\','."\n".
            '];');

        $this->artisan('translations:check', [
            'target' => 'da',
        ])->assertExitCode(0);

        $this->assertStringNotContainsString('Velkommen\/Hej', file_get_contents($this->translationFile));
        $this->assertStringContainsString('Welcome/Hi', file_get_contents($this->translationFile));
        $this->assertStringContainsString('Velkommen/Hej', file_get_contents($this->translationFile));
    }
}
