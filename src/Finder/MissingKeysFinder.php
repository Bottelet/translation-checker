<?php

namespace Bottelet\TranslationChecker\Finder;

use Bottelet\TranslationChecker\Dto\MissingTranslation;
use Bottelet\TranslationChecker\Dto\MissingTranslationList;
use Bottelet\TranslationChecker\Dto\Translation;
use Bottelet\TranslationChecker\Dto\TranslationList;
use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use SplFileInfo;

class MissingKeysFinder
{
    /**
     * Finds translatable strings in a set of files.
     *
     * @param  array<int, SplFileInfo>  $files
     */
    public function findTranslatableStrings(array $files): TranslationList
    {
        $translationList = new TranslationList();

        foreach ($files as $file) {
            if ($file->isFile()) {
                $extractor = ExtractorFactory::createExtractorForFile($file);
                $translationKeys = $extractor->extractFromFile($file);
                foreach ($translationKeys as $key) {
                    $translationList->addTranslation(new Translation($key, $file->getPathname()));
                }
            }
        }
        $persistentKeys = (new PersistentKeysManager)->getKeys();

        foreach ($persistentKeys as $key) {
            $translationList->addTranslation(new Translation($key, config_path('translator')));
        }

        return $translationList;
    }

    /**
     * Finds missing translatable strings in a set of files.
     * @param  array<int, SplFileInfo>  $files
     * @param  array<string, string>  $existingTranslatedStrings
     *
     */
    public function findMissingTranslatableStrings(array $files, array $existingTranslatedStrings): MissingTranslationList
    {
        $translationString = $this->findTranslatableStrings($files);

        return $this->extractMissingTranslations($translationString, $existingTranslatedStrings);
    }

    /**
     * @param  array<string, string>  $jsonTranslations
     */
    protected function extractMissingTranslations(TranslationList $foundStrings, array $jsonTranslations): MissingTranslationList
    {
        $missingTranslations = new MissingTranslationList();
        foreach ($foundStrings->getKeys() as $key => $string) {
            $string = stripslashes($key);
            if (! array_key_exists($string, $jsonTranslations)) {
                $missingTranslations->addTranslation(new MissingTranslation($string, null));
            }
        }

        return $missingTranslations;
    }
}
