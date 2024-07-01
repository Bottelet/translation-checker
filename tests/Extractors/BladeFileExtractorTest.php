<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;

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
}
