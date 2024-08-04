<?php

namespace Bottelet\TranslationChecker;

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
     * @param  string  $targetLanguagePath
     *
     * @return array<string, string>
     */
    public function findMissingTranslations(array $sourceFilePaths, string $targetLanguagePath): array
    {
        $files = $this->fileManagement->getAllFiles($sourceFilePaths);
        $jsonTranslations = $this->languageFileManager->readJsonFile($targetLanguagePath);
        $jsonTranslations = array_filter($jsonTranslations, function ($value) {
            return is_string($value);
        });

        return $this->missingKeysFinder->findMissingTranslatableStrings($files, $jsonTranslations);
    }
}