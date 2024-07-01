<?php

namespace Tests\Bottelet\TranslationChecker\Extractor;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use Bottelet\TranslationChecker\Extractor\RegexExtractor;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class ExtractorFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createBladeFileExtractor(): void
    {
        $file = new SplFileInfo('example.blade.php');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(BladeFileExtractor::class, $extractor);
    }

    /**
     * @test
     */
    public function createPhpClassExtractor(): void
    {
        $file = new SplFileInfo('example.php');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(PhpClassExtractor::class, $extractor);
    }

    /**
     * @test
     */
    public function createRegexExtractorForVue(): void
    {
        $file = new SplFileInfo('example.vue');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(RegexExtractor::class, $extractor);
    }

    /**
     * @test
     */
    public function createRegexExtractorForNonPhpFiles(): void
    {
        $file = new SplFileInfo('example.txt');
        $extractor = ExtractorFactory::createExtractorForFile($file);
        $this->assertInstanceOf(RegexExtractor::class, $extractor);
    }
}
