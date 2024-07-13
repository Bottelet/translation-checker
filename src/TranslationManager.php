<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Translator\TranslatorContract;

class TranslationManager
{
    public function __construct(
        protected FileManagement $fileManagement,
        protected TranslationFinder $translationFinder,
        protected JsonTranslationFileManager $jsonTranslationManager,
        protected TranslatorContract $translationService
    ) {
    }

    /**
     * @param  array<string>  $sourceFilePaths
     * @return array<string, string>
     */
    public function updateTranslationsFromFile(array $sourceFilePaths, string $targetJsonPath, ?string $targetLanguage = null, bool $translateMissing = false, string $sourceLanaguage = 'en'): array
    {
        $files = $this->fileManagement->getAllFiles($sourceFilePaths);
        $foundStrings = $this->translationFinder->findTranslatableStrings($files);
        $jsonTranslations = $this->jsonTranslationManager->readJsonFile($targetJsonPath);

        $jsonTranslations = array_filter($jsonTranslations, function ($value) {
            return is_string($value);
        });

        $missingTranslations = $this->extractMissingTranslations($foundStrings['all'], $jsonTranslations);

        if ($translateMissing && $targetLanguage !== null) {
            $missingTranslations = $this->translateMissingKeys($missingTranslations, $targetLanguage, $sourceLanaguage);
        }

        $this->updateJsonFileWithTranslations($targetJsonPath, $jsonTranslations, $missingTranslations);

        return $missingTranslations;
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

    /**
     * @param  array<string, string>  $missingTranslations
     * @return array<string, string>
     */
    protected function translateMissingKeys(array $missingTranslations, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $keys = array_keys($missingTranslations);
        $translatedKeys = $this->translationService->translateBatch($keys, $targetLanguage, $sourceLanguage);

        foreach ($translatedKeys as $index => $translatedKey) {
            $originalKey = $keys[$index];
            $missingTranslations[$originalKey] = $translatedKey ?: '';
        }

        return $missingTranslations;
    }

    /**
     * @param  array<string, string>  $existingTranslations
     * @param  array<string, string>  $newTranslations
     */
    protected function updateJsonFileWithTranslations(string $filePath, array $existingTranslations, array $newTranslations): void
    {
        $updatedTranslations = array_merge($existingTranslations, $newTranslations);

        $this->jsonTranslationManager->updateJsonFile($filePath, $updatedTranslations);
    }
}
