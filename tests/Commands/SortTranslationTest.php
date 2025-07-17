<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class SortTranslationTest extends TestCase
{
    private string $translationFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->createJsonTranslationFile('da', [
            'z' => 'Z value',
            'b' => 'B value',
            'a' => 'A value',
            'c' => 'value',
            'e' => 'value',
            'd' => 'value',
        ]);

        Config::set('translator.language_folder', $this->tempDir . '/lang');
    }

    #[Test]
    public function itSortsTranslationFileInAlphabeticOrder(): void
    {
        $this->artisan('translations:sort', [
            '--source' => 'da',
        ])->assertExitCode(0);

        $sortedContent = json_decode(file_get_contents($this->translationFile), true);

        $expectedContent = [
            'a' => 'A value',
            'b' => 'B value',
            'c' => 'value',
            'd' => 'value',
            'e' => 'value',
            'z' => 'Z value',
        ];

        $this->assertSame($expectedContent, $sortedContent);
    }

    #[Test]
    public function allTranslationFiles(): void
    {
        $secondTranslationFile = $this->createJsonTranslationFile('es', [
            'Aba' => 'value',
            'Cbb' => 'value',
            'ILL' => 'value',
            'Ace' => 'value',
            'Dff' => 'value',
            'Arc' => 'value',
        ]);

        $this->artisan('translations:sort', [
            '--all' => true,
        ])->assertExitCode(0);

        $sortedContentDaFile = json_decode(file_get_contents($this->translationFile), true);
        $sortedContentEsFile = json_decode(file_get_contents($secondTranslationFile), true);

        $expectedContentDaFile = [
            'a' => 'A value',
            'b' => 'B value',
            'c' => 'value',
            'd' => 'value',
            'e' => 'value',
            'z' => 'Z value',
        ];
        $expectedContentEsFile = [
            'Aba' => 'value',
            'Ace' => 'value',
            'Arc' => 'value',
            'Cbb' => 'value',
            'Dff' => 'value',
            'ILL' => 'value',
        ];

        $this->assertSame($expectedContentDaFile, $sortedContentDaFile);
        $this->assertSame($expectedContentEsFile, $sortedContentEsFile);
    }

    #[Test]
    public function itSortsNestedPhpFiles(): void
    {
        // Create unsorted nested PHP translation files directly
        $this->createNestedTranslationFile('nl', 'home', [
            'zitem' => 'Z Item NL',
            'aitem' => 'A Item NL',
            'citem' => 'C Item NL',
            'bitem' => 'B Item NL',
        ]);

        $this->createNestedTranslationFile('nl', 'dashboard', [
            'zmetric' => 'Z Metric NL',
            'ametric' => 'A Metric NL',
            'cmetric' => 'C Metric NL',
            'bmetric' => 'B Metric NL',
        ]);

        $this->createNestedTranslationFile('nl', 'general', [
            'zsimple' => 'Z Simple NL',
            'asimple' => 'A Simple NL',
        ]);

        // Run sort with nested flag
        Artisan::call('translations:sort', [
            '--source' => 'nl',
            '--nested' => true,
        ]);

        // Verify PHP files are sorted alphabetically
        $this->assertNestedFileSorted('nl', 'home');
        $this->assertNestedFileSorted('nl', 'dashboard');
        $this->assertNestedFileSorted('nl', 'general');

        // Verify content is preserved
        $expectedHomeContents = [
            'aitem' => 'A Item NL',
            'bitem' => 'B Item NL',
            'citem' => 'C Item NL',
            'zitem' => 'Z Item NL',
        ];
        $this->assertNestedFileContains('nl', 'home', $expectedHomeContents);

        $expectedDashboardContents = [
            'ametric' => 'A Metric NL',
            'bmetric' => 'B Metric NL',
            'cmetric' => 'C Metric NL',
            'zmetric' => 'Z Metric NL',
        ];
        $this->assertNestedFileContains('nl', 'dashboard', $expectedDashboardContents);

        $expectedGeneralContents = [
            'asimple' => 'A Simple NL',
            'zsimple' => 'Z Simple NL',
        ];
        $this->assertNestedFileContains('nl', 'general', $expectedGeneralContents);
    }

    #[Test]
    public function itSortsNestedPhpFilesWithAllOption(): void
    {
        // Create unsorted nested PHP translation files for multiple languages
        $this->createNestedTranslationFile('fr', 'home', [
            'zitem' => 'Z Item FR',
            'aitem' => 'A Item FR',
        ]);

        $this->createNestedTranslationFile('it', 'dashboard', [
            'zmetric' => 'Z Metric IT',
            'ametric' => 'A Metric IT',
        ]);

        // Run sort with nested and all flags
        Artisan::call('translations:sort', [
            '--nested' => true,
            '--all' => true,
        ]);

        // Verify PHP files for all languages are sorted alphabetically
        $this->assertNestedFileSorted('fr', 'home');
        $this->assertNestedFileSorted('it', 'dashboard');

        // Verify content is preserved
        $this->assertNestedFileContains('fr', 'home', [
            'aitem' => 'A Item FR',
            'zitem' => 'Z Item FR',
        ]);

        $this->assertNestedFileContains('it', 'dashboard', [
            'ametric' => 'A Metric IT',
            'zmetric' => 'Z Metric IT',
        ]);
    }
}
