<?php

namespace Bottelet\TranslationChecker\Finder;

use Bottelet\TranslationChecker\Dto\TranslationCollection;
use Bottelet\TranslationChecker\Dto\TranslationItem;
use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use SplFileInfo;

class MissingKeysFinder
{
    /**
     * Finds translatable strings in a set of files.
     *
     * @param  array<int, SplFileInfo>  $files
     */
    public function findTranslatableStrings(array $files): TranslationCollection
    {
        $translationList = new TranslationCollection();

        foreach ($files as $file) {
            if ($file->isFile()) {
                $extractor = ExtractorFactory::createExtractorForFile($file);
                $translationKeys = $extractor->extractFromFile($file);
                foreach ($translationKeys as $key) {
                    $translationList->addTranslation(new TranslationItem($key, $file->getPathname()));
                }
            }
        }
        $persistentKeys = (new PersistentKeysManager)->getKeys();

        foreach ($persistentKeys as $key) {
            $translationList->addTranslation(new TranslationItem($key, config_path('translator')));
        }

        return $translationList;
    }

    /**
     * Finds missing translatable strings in a set of files.
     * @param  array<int, SplFileInfo>  $files
     * @param  array<string, string>  $existingTranslatedStrings
     */
    public function findMissingTranslatableStrings(array $files, array $existingTranslatedStrings): TranslationCollection
    {
        $translationString = $this->findTranslatableStrings($files);

        return $this->extractMissingTranslations($translationString, $existingTranslatedStrings);
    }

    /**
     * @param  array<string, string>  $jsonTranslations
     */
    protected function extractMissingTranslations(TranslationCollection $foundStrings, array $jsonTranslations): TranslationCollection
    {
        $missingTranslations = new TranslationCollection();
        foreach ($foundStrings->getKeys() as $key => $string) {
            $string = stripslashes($key);
            if (! array_key_exists($string, $jsonTranslations)) {
                $missingTranslations->addTranslation(new TranslationItem($string));
            }
        }

        return $missingTranslations;
    }
}
