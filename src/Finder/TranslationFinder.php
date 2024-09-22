<?php

namespace Bottelet\TranslationChecker\Finder;

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
     *
     * @return array<string, string|null>
     */
    public function findMissingTranslations(array $sourceFilePaths): array
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
     *
     * @return array<int, string>
     */
    public function findAllTranslations(array $sourceFilePaths): array
    {
        return $this->missingKeysFinder->findTranslatableStrings($this->fileManagement->getAllFiles($sourceFilePaths));
    }
}
