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
        //TODO Can we remove everything but ALL? And perhaps not make it multi dimensional
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

    /**
     * @param  array<int, SplFileInfo>  $files
     * @param  array<string, string>  $currentTranslatedStrings
     *
     * @return void
     */
    public function findMissingTranslableStrings(array $files, array $currentTranslatedStrings): array
    {
        $translationString = $this->findTranslatableStrings($files);
        return $this->extractMissingTranslations($translationString['all'], $currentTranslatedStrings);
    }

    /**
     * @param  array<string>  $foundStrings
     * @param  array<string, string>  $jsonTranslations
     * @return array<string, string>
     */
    protected function extractMissingTranslations(array $foundStrings, array $jsonTranslations): array
    {
        $missingTranslations = [];
        foreach ($foundStrings as $string) {
            $unescapedString = stripslashes($string);
            if (! array_key_exists($unescapedString, $jsonTranslations)) {
                $missingTranslations[$unescapedString] = '';
            }
        }

        return $missingTranslations;
    }
}
