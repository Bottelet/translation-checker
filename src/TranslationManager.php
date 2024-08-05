<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Sort\SorterContract;
use Bottelet\TranslationChecker\Translator\TranslatorContract;

class TranslationManager
{
    public function __construct(
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
        $translationFinder = new TranslationFinder(new FileManagement, new LanguageFileManager($targetJsonPath), new MissingKeysFinder);

        $missingTranslations = $translationFinder->findMissingTranslations($sourceFilePaths);

        if ($translateMissing && $targetLanguage !== null) {
            $missingTranslations = $this->translationService->translateBatch(array_keys($missingTranslations), $targetLanguage, $sourceLanguage);
        }

        /** @var array<string, string> $allTranslations */
        $allTranslations = array_merge($translationFinder->getLanguageFilerManager()->readJsonFile(), $missingTranslations);

        if($sort) {
            $allTranslations = $this->sorter->sortByKey($allTranslations);
        }

        $translationFinder->getLanguageFilerManager()->updateJsonFile($allTranslations);

        return $missingTranslations;
    }
}
