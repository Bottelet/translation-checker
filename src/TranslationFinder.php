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
     * @return array<int, string>
     */
    public function findTranslatableStrings(array $files): array
    {
        $found = [];

        foreach ($files as $file) {
            if ($file->isFile()) {
                $extractor = ExtractorFactory::createExtractorForFile($file);
                $translationKeys = $extractor->extractFromFile($file);
                foreach ($translationKeys as $key) {
                    $found[] = $key;
                }
            }
        }

        return $found;
    }

    /**
     * @param  array<int, SplFileInfo>  $files
     * @param  array<string, string>  $currentTranslatedStrings
     *
     * @return array<string, string>
     */
    public function findMissingTranslatableStrings(array $files, array $currentTranslatedStrings): array
    {
        $translationString = $this->findTranslatableStrings($files);
        return $this->extractMissingTranslations($translationString, $currentTranslatedStrings);
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
