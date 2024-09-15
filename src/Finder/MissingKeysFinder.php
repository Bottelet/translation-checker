<?php

namespace Bottelet\TranslationChecker\Finder;

use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use SplFileInfo;

class MissingKeysFinder
{
    /**
     * Finds translatable strings in a set of files.
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
     * Finds missing translatable strings in a set of files.
     * @param  array<int, SplFileInfo>  $files
     * @param  array<string, string>  $existingTranslatedStrings
     *
     * @return array<string, string>
     */
    public function findMissingTranslatableStrings(array $files, array $existingTranslatedStrings): array
    {
        $translationString = $this->findTranslatableStrings($files);

        return $this->extractMissingTranslations($translationString, $existingTranslatedStrings);
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
