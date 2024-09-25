<?php

namespace Bottelet\TranslationChecker\Extractor;

use SplFileInfo;

class ExtractorFactory
{
    public static function createExtractorForFile(SplFileInfo $file): ExtractorContract
    {
        if (str_ends_with($file->getFilename(), '.blade.php')) {
            return new BladeFileExtractor;
        }

        if ($file->getExtension() === 'php') {
            return new PhpClassExtractor;
        }

        return app(RegexExtractor::class);
    }
}
