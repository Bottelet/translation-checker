<?php

declare(strict_types=1);

namespace Bottelet\TranslationChecker\Translator;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use DeepL\Translator;

class DeeplTranslator implements TranslatorContract
{
    public function __construct(
        protected VariableRegexHandler $variableHandler,
        protected Translator $translateClient,
    ) {
    }

    /**
     * @throws \DeepL\DeepLException
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $replaceVariablesText = $this->variableHandler->replacePlaceholders($text);

        $translation = $this->translateClient->translateText($replaceVariablesText, $sourceLanguage, $targetLanguage);

        return $this->variableHandler->restorePlaceholders($translation->text);
    }

    /**
     * @param  array<string>  $texts
     * @return array<string>
     *
     * @throws \DeepL\DeepLException
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $textsToTranslate = array_map([$this->variableHandler, 'replacePlaceholders'], $texts);

        $translations = $this->translateClient->translateText($textsToTranslate, $sourceLanguage, $targetLanguage);

        $translatedKeys = [];
        foreach ($translations as $index => $translation) {
            $translatedText = isset($translation->text) ? $this->variableHandler->restorePlaceholders($translation->text) : '';
            $translatedKeys[$texts[$index]] = $translatedText ?: '';
        }

        return $translatedKeys;
    }

    public function isConfigured(): bool
    {
        /** @var array<string, null|string> $deeplConfig */
        $deeplConfig = config('translator.translators.deepl');

        return !in_array(null, $deeplConfig, true);
    }
}