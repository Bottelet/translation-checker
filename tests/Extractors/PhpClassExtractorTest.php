<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class PhpClassExtractorTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    #[Test]
    public function canFindFunctionsInController(): void
    {
        $phpExtractor = new PhpClassExtractor;
        $foundStrings = $phpExtractor->extractFromFile($this->phpControllerFile);

        $this->assertCount(10, $foundStrings);
    }

    #[Test]
    public function extractFileIsEmptyIfNotTranslationFunctionInFile(): void
    {
        $file = $this->createTempFile('no-translations.php', "
            <?php
            class Test 
            { 
                public function index()
                {
                    return [Model::all()];
                }
            }
        ");
        $phpExtractor = new PhpClassExtractor();
        $foundStrings = $phpExtractor->extractFromFile($file);

        $this->assertEmpty($foundStrings);
    }
}
