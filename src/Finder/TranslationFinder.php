<?php

namespace Bottelet\TranslationChecker\Finder;

use Bottelet\TranslationChecker\Dto\MissingTranslationList;
use Bottelet\TranslationChecker\Dto\TranslationList;
use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;

class TranslationFinder
{
    public function __construct(protected FileManagement $fileManagement, protected LanguageFileManagerFactory $languageFileManager, protected MissingKeysFinder $missingKeysFinder)
    {
    }

    public function getLanguageFilerManager(): LanguageFileManagerFactory
    {
        return $this->languageFileManager;
    }

    /**
     * @param  array<string>  $sourceFilePaths
     */
    public function findMissingTranslations(array $sourceFilePaths): MissingTranslationList
    {
        $files = $this->fileManagement->getAllFiles($sourceFilePaths);
        $existingTranslations = $this->languageFileManager->readFile();
        $existingTranslations = array_filter($existingTranslations, function ($value) {
            return is_string($value);
        });

        return $this->missingKeysFinder->findMissingTranslatableStrings($files, $existingTranslations);
    }

    /**
     * @param array<string, string> $sourceFilePaths
     */
    public function findAllTranslations(array $sourceFilePaths): TranslationList
    {
        return $this->missingKeysFinder->findTranslatableStrings($this->fileManagement->getAllFiles($sourceFilePaths));
    }
}
