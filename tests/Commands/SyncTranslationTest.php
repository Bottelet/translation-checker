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

        $this->sourceFile = $this->createJsonTranslationFile('en', [
            'key1' => 'Source Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ]);

        $this->targetFile = $this->createJsonTranslationFile('de', [
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
        $thirdFile = $this->createJsonTranslationFile('pl', '{}');

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

    #[Test]
    public function itSyncsAllTranslationsWithAllFlag(): void
    {
        $thirdFile = $this->createJsonTranslationFile('fr', [
            'key1' => 'French Value 1',
        ]);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--all' => true,
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

        $expectedTranslationsFrench = [
            'key1' => 'French Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ];

        $this->assertEquals($expectedTranslationsSource, json_decode(file_get_contents($this->sourceFile), true));
        $this->assertEquals($expectedTranslationsTarget, json_decode(file_get_contents($this->targetFile), true));
        $this->assertEquals($expectedTranslationsFrench, json_decode(file_get_contents($thirdFile), true));
    }

    #[Test]
    public function itSyncsOnlyTargetWhenTargetSpecified(): void
    {
        $thirdFile = $this->createJsonTranslationFile('fr', [
            'key1' => 'French Value 1',
        ]);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--target' => 'de',
        ]);

        $expectedTranslationsTarget = [
            'key1' => 'Target Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
            'key4' => 'Target Value 4',
            'key5' => 'Target Value 5',
        ];

        $expectedTranslationsFrench = [
            'key1' => 'French Value 1',
        ];

        $this->assertEquals($expectedTranslationsTarget, json_decode(file_get_contents($this->targetFile), true));
        $this->assertEquals($expectedTranslationsFrench, json_decode(file_get_contents($thirdFile), true));
    }

    #[Test]
    public function itUsesAppLocaleAsDefaultSource(): void
    {
        Config::set('app.locale', 'en');

        $thirdFile = $this->createJsonTranslationFile('es', []);

        Artisan::call('translations:sync', [
            '--target' => 'es',
        ]);

        $expectedTranslations = [
            'key1' => 'Source Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ];

        $this->assertEquals($expectedTranslations, json_decode(file_get_contents($thirdFile), true));
    }

    #[Test]
    public function itSyncsPhpFilesToCorrespondingPhpFiles(): void
    {
        $sourceAuthFile = $this->createPhpTranslationFile('en/auth.php', [
            'failed' => 'These credentials do not match our records.',
            'password' => 'The provided password is incorrect.',
            'throttle' => 'Too many login attempts.',
        ]);

        $targetAuthFile = $this->createPhpTranslationFile('da/auth.php', [
            'failed' => 'Disse legitimationsoplysninger matcher ikke vores optegnelser.',
        ]);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--target' => 'da',
        ]);

        $expectedTranslations = [
            'failed' => 'Disse legitimationsoplysninger matcher ikke vores optegnelser.',
            'password' => 'The provided password is incorrect.',
            'throttle' => 'Too many login attempts.',
        ];

        $targetContent = $this->readPhpFile($targetAuthFile);
        $this->assertEquals($expectedTranslations, $targetContent);
    }

    #[Test]
    public function itDoesNotMixJsonAndPhpFiles(): void
    {
        $this->createPhpTranslationFile('en/auth.php', [
            'failed' => 'These credentials do not match our records.',
            'password' => 'The provided password is incorrect.',
        ]);

        $targetAuthFile = $this->createPhpTranslationFile('de/auth.php', [
            'failed' => 'Disse legitimationsoplysninger matcher nicht.',
        ]);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--target' => 'de',
        ]);

        $expectedJsonTranslations = [
            'key1' => 'Target Value 1',
            'key4' => 'Target Value 4',
            'key5' => 'Target Value 5',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ];

        $expectedPhpTranslations = [
            'failed' => 'Disse legitimationsoplysninger matcher nicht.',
            'password' => 'The provided password is incorrect.',
        ];

        $jsonContent = json_decode(file_get_contents($this->targetFile), true);
        $phpContent = $this->readPhpFile($targetAuthFile);

        $this->assertEquals($expectedJsonTranslations, $jsonContent);
        $this->assertEquals($expectedPhpTranslations, $phpContent);
    }

    #[Test]
    public function itSyncsMultipleNestedPhpFiles(): void
    {
        $this->createPhpTranslationFile('en/auth.php', [
            'failed' => 'Auth failed',
            'password' => 'Wrong password',
        ]);

        $this->createPhpTranslationFile('en/validation.php', [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email.',
        ]);

        $targetAuthFile = $this->createPhpTranslationFile('da/auth.php', [
            'failed' => 'Auth fejlede',
        ]);

        $targetValidationFile = $this->createPhpTranslationFile('da/validation.php', []);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--target' => 'da',
        ]);

        $expectedAuthTranslations = [
            'failed' => 'Auth fejlede',
            'password' => 'Wrong password',
        ];

        $expectedValidationTranslations = [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email.',
        ];

        $this->assertEquals($expectedAuthTranslations, $this->readPhpFile($targetAuthFile));
        $this->assertEquals($expectedValidationTranslations, $this->readPhpFile($targetValidationFile));
    }

    #[Test]
    public function itSyncsAllLanguagesWithMixedJsonAndPhpFiles(): void
    {
        $this->createPhpTranslationFile('en/auth.php', [
            'failed' => 'Auth failed',
        ]);

        $targetAuthFileDa = $this->createPhpTranslationFile('da/auth.php', []);
        $targetAuthFileFr = $this->createPhpTranslationFile('fr/auth.php', []);

        $targetJsonFr = $this->createJsonTranslationFile('fr', []);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--all' => true,
        ]);

        $expectedPhpTranslations = [
            'failed' => 'Auth failed',
        ];

        $expectedJsonTranslations = [
            'key1' => 'Source Value 1',
            'key2' => 'Source Value 2',
            'key3' => 'Source Value 3',
        ];

        $this->assertEquals($expectedPhpTranslations, $this->readPhpFile($targetAuthFileDa));
        $this->assertEquals($expectedPhpTranslations, $this->readPhpFile($targetAuthFileFr));
        $this->assertEquals($expectedJsonTranslations, json_decode(file_get_contents($targetJsonFr), true));
    }

    #[Test]
    public function itCreatesTargetDirectoryIfNotExists(): void
    {
        $this->createPhpTranslationFile('en/auth.php', [
            'failed' => 'Auth failed',
        ]);

        $targetAuthFile = $this->tempDir . '/lang/es/auth.php';

        $this->assertFileDoesNotExist($targetAuthFile);

        Artisan::call('translations:sync', [
            '--source' => 'en',
            '--target' => 'es',
        ]);

        $this->assertFileExists($targetAuthFile);
        $this->assertEquals(['failed' => 'Auth failed'], $this->readPhpFile($targetAuthFile));
    }

    private function readPhpFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $tempFile = tempnam(sys_get_temp_dir(), 'phptest');
        file_put_contents($tempFile, $content);
        $result = require $tempFile;
        unlink($tempFile);
        return $result;
    }
}
