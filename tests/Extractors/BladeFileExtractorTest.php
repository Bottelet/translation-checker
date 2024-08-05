<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class BladeFileExtractorTest extends TestCase
{
    #[Test]
    public function ensureFileIsCompilable(): void
    {
        $bladeContent = file_get_contents($this->bladeFile->getRealPath());
        $compiledString = Blade::compileString($bladeContent);
        $this->assertNotEmpty($compiledString);
    }

    #[Test]
    public function canFindFunctionsInBlade(): void
    {
        $phpExtractor = new BladeFileExtractor;
        $foundStrings = $phpExtractor->extractFromFile($this->bladeFile);

        $this->assertContains('This is a demo page to showcase translations and Blade components.', $foundStrings);
        $this->assertContains('You are currently not logged in.', $foundStrings);
    }

    #[Test]
    public function emptyArrayOnEmptyBladeFile()
    {
        $phpExtractor = new BladeFileExtractor;
        $bladeFilePath = $this->tempDir . '/empty-test.blade.php';
        file_put_contents($bladeFilePath, null);
        $bladeFile = new SplFileInfo($bladeFilePath);

        $foundStrings = $phpExtractor->extractFromFile($bladeFile);
        $this->assertEmpty($foundStrings);
    }
}
