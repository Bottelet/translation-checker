<?php

namespace Tests\Unit\Extractor;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use SplFileInfo;
use Tests\TestCase;

class BladeFileExtractorTest extends TestCase
{
    private string $tempDir;

    private SplFileInfo $bladeFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/blade_file_extractor_tests';
        if (! file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $bladeFilePath = $this->tempDir . '/test.blade.php';
        $bladePath = 'translation-checker/tests/templates/underscore-translations.blade.php';
        file_put_contents($bladeFilePath, file_get_contents($bladePath));

        $this->bladeFile = new SplFileInfo($bladeFilePath);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->tempDir}/*.*"));
        rmdir($this->tempDir);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function canFindFunctionsInBlade(): void
    {
        $phpExtractor = new BladeFileExtractor;
        $foundStrings = $phpExtractor->extractFromFile($this->bladeFile);

        $this->assertContains('This is a demo page to showcase translations and Blade components.', $foundStrings);
        $this->assertContains('You are currently not logged in.', $foundStrings);

    }
}
