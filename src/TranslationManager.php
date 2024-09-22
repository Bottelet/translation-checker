<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Exception\TranslationServiceException;
use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;
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
     * @return array<string, string|null>
     */
    public function updateTranslationsFromFile(array $sourceFilePaths, string $targetJsonPath, bool $sort = false, ?string $targetLanguage = null, bool $translateMissing = false, string $sourceLanguage = 'en'): array
    {
        $translationFinder = new TranslationFinder(new FileManagement, new LanguageFileManagerFactory($targetJsonPath), new MissingKeysFinder);

        $missingTranslations = $translationFinder->findMissingTranslations($sourceFilePaths);

        if ($translateMissing && $targetLanguage !== null) {
            if (!$this->translationService->isConfigured()) {
                throw TranslationServiceException::notConfigured(get_class($this->translationService));
            }
            $missingTranslations = $this->translationService->translateBatch(array_keys($missingTranslations), $targetLanguage, $sourceLanguage);
        }

        /** @var array<string, string> $allTranslations */
        $allTranslations = array_merge($translationFinder->getLanguageFilerManager()->readFile(), $missingTranslations);

        if ($sort) {
            $allTranslations = $this->sorter->sortByKey($allTranslations);
        }

        $translationFinder->getLanguageFilerManager()->updateFile($allTranslations);

        return $missingTranslations;
    }
}
