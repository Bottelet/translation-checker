<?php

namespace Bottelet\TranslationChecker\Translator;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class FreeGoogleTranslator implements TranslatorContract
{
    public function __construct(
        protected VariableRegexHandler $variableHandler,
        protected GoogleTranslate      $translateClient,
    ) {
    }

    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $replaceVariablesText = $this->variableHandler->replacePlaceholders($text);

        $translation = $this->translateClient
            ->setSource($sourceLanguage)
            ->setTarget($targetLanguage)
            ->translate($replaceVariablesText);

        if (!isset($translation)) {
            return '';
        }

        return $this->variableHandler->restorePlaceholders($translation);
    }

    /**
     * @param array<string> $texts Array of texts to translate.
     * @param string $targetLanguage
     * @param string $sourceLanguage
     * @return array<string, string> Array of translated texts.
     *
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $textsToTranslate = array_map([$this->variableHandler, 'replacePlaceholders'], $texts);

        $translations = [];
        foreach ($textsToTranslate as $text) {
            $translations[] = $this->translateClient
                ->setSource($sourceLanguage)
                ->setTarget($targetLanguage)
                ->translate($text);
        }

        $translatedKeys = [];
        foreach ($translations as $index => $translation) {
            $translatedText = $translation ? $this->variableHandler->restorePlaceholders($translation) : '';
            $translatedKeys[$texts[$index]] = $translatedText;
        }

        return $translatedKeys;
    }

    public function isConfigured(): bool
    {
        /** @var array<string, null|string> $freeGoogleConfig */
        $freeGoogleConfig = config('translator.translators.free_google');

        return !in_array(null, $freeGoogleConfig, true);
    }
}
