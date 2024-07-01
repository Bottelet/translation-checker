<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use SplFileInfo;

class TranslationFinder
{
    /**
     * Finds translatable strings in a set of files and organizes them by categories.
     *
     * @param  array<int, SplFileInfo>  $files
     * @return array<string, array<string>>
     */
    public function findTranslatableStrings(array $files): array
    {
        $found = [
            'all' => [],
            'simpleStrings' => [],
            'stringsWithVariables' => [],
            'vueSyntax' => [],
            'nonStrings' => [],
        ];

        foreach ($files as $file) {
            if ($file->isFile()) {
                $extractor = ExtractorFactory::createExtractorForFile($file);
                $translationKeys = $extractor->extractFromFile($file);
                $found['all'] = array_merge($found['all'], $translationKeys);
            }
        }

        return $found;
    }
}
