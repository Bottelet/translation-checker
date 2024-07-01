<?php

namespace Tests\Bottelet\TranslationChecker\Extractor;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use Bottelet\TranslationChecker\Extractor\RegexExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class ExtractorFactoryTest extends TestCase
{
    #[Test]
    public function createBladeFileExtractor(): void
    {
        $file = new SplFileInfo('example.blade.php');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(BladeFileExtractor::class, $extractor);
    }

    #[Test]
    public function createPhpClassExtractor(): void
    {
        $file = new SplFileInfo('example.php');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(PhpClassExtractor::class, $extractor);
    }

    #[Test]
    public function createRegexExtractorForVue(): void
    {
        $file = new SplFileInfo('example.vue');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(RegexExtractor::class, $extractor);
    }

    #[Test]
    public function createRegexExtractorForNonPhpFiles(): void
    {
        $file = new SplFileInfo('example.txt');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(RegexExtractor::class, $extractor);
    }
}
