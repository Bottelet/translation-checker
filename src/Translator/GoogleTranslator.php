<?php

namespace Bottelet\TranslationChecker\Translator;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslator implements TranslatorContract
{
    private TranslateClient $translateClient;

    /**
     * @throws GoogleException
     */
    public function __construct(
        protected VariableRegexHandler $variableHandler
    ) {
        $this->translateClient = new TranslateClient(['keyFile' => config('services.google-translate')]);
    }

    /**
     * @throws ServiceException
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $replaceVariablesText = $this->variableHandler->replacePlaceholders($text);

        $translation = $this->translateClient->translate($replaceVariablesText, [
            'target' => $targetLanguage,
            'model' => 'nmt',
            'source' => $sourceLanguage,
        ]);
        $translation = $this->variableHandler->restorePlaceholders($translation['text']);

        return $translation['text'];
    }

    /**
     * @throws ServiceException
     */
    public function translateBatch(array $text, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $textsToTranslate = [];

        foreach ($text as $string) {
            $textsToTranslate[] = $this->variableHandler->replacePlaceholders($string);
        }

        $translations = $this->translateClient->translateBatch($textsToTranslate, [
            'target' => $targetLanguage,
            'model' => 'nmt',
            'source' => $sourceLanguage,
        ]);

        $translated = [];
        foreach ($translations as $translatedText) {
            $translated['text'][] = $this->variableHandler->restorePlaceholders($translatedText['text']);
        }

        return $translated['text'];
    }
}
