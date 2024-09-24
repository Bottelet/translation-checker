<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class SyncTranslationsForPhpFilesTest extends TestCase
{
    private string $sourceFile;
    private string $targetFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceFile = $this->createPhpTranslationFile('en.php', [
            'key1' => 'Source Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ]);

        $this->targetFile = $this->createPhpTranslationFile('de.php', [
            'key1' => 'Target Value 1',
            'key4' => 'Target Value 4',
            'key5' => 'Target Value 5',
        ]);

        Config::set('translator.language_folder', $this->tempDir.'/lang');
    }

    #[Test]
    public function itSyncsTranslationsFromSourceToTarget(): void
    {
        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--target' => 'de',
        ]);

        $expectedTranslations = [
            'key1' => 'Target Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
            'key4' => 'Target Value 4',
            'key5' => 'Target Value 5',
        ];

        $this->assertEquals($expectedTranslations, require $this->targetFile);
    }

    #[Test]
    public function itSyncsAllTranslationsIfNoTarget(): void
    {
        $thirdFile = $this->createPhpTranslationFile('pl.php', []);

        Artisan::call('translations:sync', [
            '--source' => 'en',
        ]);

        $expectedTranslationsSource = [
            'key1' => 'Source Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ];

        $expectedTranslationsTarget = [
            'key1' => 'Target Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
            'key4' => 'Target Value 4',
            'key5' => 'Target Value 5',
        ];

        $this->assertEquals($expectedTranslationsSource, require $this->sourceFile);
        $this->assertEquals($expectedTranslationsTarget, require $this->targetFile);
        $this->assertEquals($expectedTranslationsSource, require $thirdFile);
    }
}
