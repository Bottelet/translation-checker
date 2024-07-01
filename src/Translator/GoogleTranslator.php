<?php

namespace Bottelet\TranslationChecker\Translator;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslator implements TranslatorContract
{
    private TranslateClient $translateClient;

    public function __construct(
        protected VariableRegexHandler $variableHandler
    ) {
        $this->translateClient = new TranslateClient(['key' => getenv('GOOGLE_TRANSLATE_API_KEY')]);
    }

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $replaceVariablesText = $this->variableHandler->replacePlaceholders($text);

        $translation = $this->translateClient->translate($replaceVariablesText, [
            'target' => $targetLanguage,
            'model' => 'nmt',
            'source' => $sourceLanguage,
        ]);

        // Check if 'text' key exists in the translation array
        if (! isset($translation['text'])) {
            // Handle the case where 'text' key is missing or return a default/fallback value
            return ''; // Or use some fallback mechanism
        }

        return $this->variableHandler->restorePlaceholders($translation['text']);
    }

    /**
     * Translates an array of strings from the source language to the target language.
     *
     * @param  array<string>  $texts Array of texts to translate.
     * @return array<string> Array of translated texts.
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $textsToTranslate = array_map([$this->variableHandler, 'replacePlaceholders'], $texts);

        $translations = $this->translateClient->translateBatch($textsToTranslate, [
            'target' => $targetLanguage,
            'model' => 'nmt',
            'source' => $sourceLanguage,
        ]);

        return array_map(function ($translation) {
            return isset($translation['text']) ? $this->variableHandler->restorePlaceholders($translation['text']) : '';
        }, $translations);
    }
}
