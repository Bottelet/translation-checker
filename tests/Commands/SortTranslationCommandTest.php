<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Artisan;

class SortTranslationCommandTest extends TestCase
{

    private string $translationFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationFile = $this->createTranslationFile('da', [
            'z' => 'Z value',
            'b' => 'B value',
            'a' => 'A value',
            'c' => 'value',
            'e' => 'value',
            'd' => 'value',
        ]);

        Config::set('translator.language_folder', $this->tempDir.'/lang');
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
        $secondTranslationFile = $this->createTranslationFile('es', [
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
}
