<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Sort\SorterContract;
use Bottelet\TranslationChecker\Translator\TranslatorContract;

class TranslationManager
{
    public function __construct(
        protected FileManagement $fileManagement,
        protected MissingKeysFinder $missingKeysFinder,
        protected LanguageFileManager $jsonTranslationManager,
        protected SorterContract $sorter,
        protected TranslatorContract $translationService
    ) {
    }

    /**
     * @param  array<string>  $sourceFilePaths
     * @return array<string, string>
     */
    public function updateTranslationsFromFile(array $sourceFilePaths, string $targetJsonPath, bool $sort = false, ?string $targetLanguage = null, bool $translateMissing = false, string $sourceLanguage = 'en'): array
    {
        $files = $this->fileManagement->getAllFiles($sourceFilePaths);
        $jsonTranslations = $this->jsonTranslationManager->readJsonFile($targetJsonPath);
        $jsonTranslations = array_filter($jsonTranslations, function ($value) {
            return is_string($value);
        });
        $missingTranslations = $this->missingKeysFinder->findMissingTranslatableStrings($files, $jsonTranslations);
        /// Perhaps turn this part into a findMissingTranslations / Make it in the TranslationsFinder
        if ($translateMissing && $targetLanguage !== null) {
            $missingTranslations = $this->translationService->translateBatch(array_keys($missingTranslations), $targetLanguage, $sourceLanguage);
        }

        $allTranslations = array_merge($jsonTranslations, $missingTranslations);

        if($sort) {
            $allTranslations = $this->sorter->sortByKey($allTranslations);
        }

        $this->updateJsonFileWithTranslations($targetJsonPath, $allTranslations);

        return $missingTranslations;
    }

    /**
     * @param  string  $filePath
     * @param  array<string, string>  $updatedTranslations
     */
    protected function updateJsonFileWithTranslations(string $filePath, array $updatedTranslations): void
    {

        $this->jsonTranslationManager->updateJsonFile($filePath, $updatedTranslations);
    }
}
