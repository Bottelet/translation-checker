<?php

namespace Bottelet\TranslationChecker\Tests\Commands;

use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
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
        Config::set('translator.language_folder', $this->tempDir . '/lang');
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
    public function itCleanNestedTranslationsCorrectly(): void
    {
        file_put_contents($this->tempDir . '/controller.php', '<?php 
        echo __("home.welcome");
        echo __("admin.users.list");
        ?>');

        // Set up all nested translation files with existing values
        $this->createNestedTranslationFile('en', 'home', [
            'welcome' => 'Welcome to our home',
            'unused_key' => 'This should be removed',
            'another_unused' => 'Also should be removed',
        ]);

        $this->createNestedTranslationFile('en', 'admin', [
            'users.list' => 'User List',
            'users.create' => 'Create User', // Should be removed (unused)
            'settings' => 'Settings', // Should be removed (unused)
        ]);

        $this->createNestedTranslationFile('en', 'dashboard', [
            'analytics.visits' => 'Visits', // Should be removed (entire file, unused)
            'analytics.users' => 'Users',
        ]);

        $this->createNestedTranslationFile('en', 'general', [
            'simple' => 'Simple value', // Should be removed (entire file, unused)
        ]);

        // Run clean with nested flag
        Artisan::call('translations:clean', [
            '--source' => 'en',
            '--nested' => true,
        ]);

        // Check that used translations still exist
        $this->assertNestedFileContains('en', 'home', ['welcome' => 'Welcome to our home']);
        $this->assertNestedFileContains('en', 'admin', ['users.list' => 'User List']);

        // Check that unused keys were removed
        $homeFile = require $this->tempDir . '/lang/en/home.php';
        $this->assertArrayNotHasKey('unused_key', $homeFile);
        $this->assertArrayNotHasKey('another_unused', $homeFile);

        $adminFile = require $this->tempDir . '/lang/en/admin.php';
        $this->assertArrayNotHasKey('users.create', $adminFile);
        $this->assertArrayNotHasKey('settings', $adminFile);

        // Check that unused translation files were removed
        $dashboardFilePath = $this->tempDir . '/lang/en/dashboard.php';
        $this->assertFileDoesNotExist($dashboardFilePath);

        $generalFilePath = $this->tempDir . '/lang/en/general.php';
        $this->assertFileDoesNotExist($generalFilePath);
    }
}
