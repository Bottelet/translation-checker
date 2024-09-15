<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class SyncTranslationTest extends TestCase
{
    private string $sourceFile;
    private string $targetFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceFile = $this->createTranslationFile('en', [
            'key1' => 'Source Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ]);

        $this->targetFile = $this->createTranslationFile('de', [
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

        $this->assertEquals($expectedTranslations, json_decode(file_get_contents($this->targetFile), true));
    }

    #[Test]
    public function itSyncsAllTranslationsIfNoTarget(): void
    {
        $thirdFile = $this->createTranslationFile('pl', '');

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

        $this->assertEquals($expectedTranslationsSource, json_decode(file_get_contents($this->sourceFile), true));
        $this->assertEquals($expectedTranslationsTarget, json_decode(file_get_contents($this->targetFile), true));
        $this->assertEquals($expectedTranslationsSource, json_decode(file_get_contents($thirdFile), true));
    }
}
