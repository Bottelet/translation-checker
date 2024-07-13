<?php

namespace Bottelet\TranslationChecker\Translator;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslator implements TranslatorContract
{
    private TranslateClient $translateClient;

    public function __construct(
        protected VariableRegexHandler $variableHandler
    ) {
        $this->translateClient = new TranslateClient(['key' => config('translator.translators.google')]);
    }

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $replaceVariablesText = $this->variableHandler->replacePlaceholders($text);

        $translation = $this->translateClient->translate($replaceVariablesText, [
            'target' => $targetLanguage,
            'model' => 'nmt',
            'source' => $sourceLanguage,
        ]);

        if (! isset($translation['text'])) {
            return '';
        }

        return $this->variableHandler->restorePlaceholders($translation['text']);
    }

    /**
     * @param  array<string>  $texts Array of texts to translate.
     * @return array<string> Array of translated texts.
     *
     * @throws ServiceException
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
