<?php

namespace Bottelet\TranslationChecker\Finder;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManager;

class TranslationFinder
{
    public function __construct(protected FileManagement $fileManagement, protected LanguageFileManager $languageFileManager, protected MissingKeysFinder $missingKeysFinder)
    {
    }

    public function getLanguageFilerManager():LanguageFileManager
    {
        return $this->languageFileManager;
    }

    /**
     * @param  array<string>  $sourceFilePaths
     *
     * @return array<string, string>
     */
    public function findMissingTranslations(array $sourceFilePaths): array
    {
        $files = $this->fileManagement->getAllFiles($sourceFilePaths);
        $existingTranslations = $this->languageFileManager->readJsonFile();
        $existingTranslations = array_filter($existingTranslations, function ($value) {
            return is_string($value);
        });

        return $this->missingKeysFinder->findMissingTranslatableStrings($files, $existingTranslations);
    }
}