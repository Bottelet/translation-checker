<?php

namespace Bottelet\TranslationChecker\Translator;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;

class OpenAiTranslator implements TranslatorContract
{
    public function __construct(
        protected ClientContract $translateClient,
    ) {
    }

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $systemPrompt = "You are a professional translator. Your task is to translate a single text string from {$sourceLanguage} to {$targetLanguage}.
Instructions:
1. Translate the entire string from {$sourceLanguage} to {$targetLanguage}.
2. Words prefixed with a colon (:) are special tokens. Do not translate these tokens, keep them as is.
3. Maintain the original structure and formatting of the input string.

Input format: A single string in {$sourceLanguage}, potentially containing words prefixed with colons.

Output format: respond with a single string in {$targetLanguage}, potentially containing words prefixed with colons.";

        $translation = $this->translateClient->chat()->create([
            'model' => config('translator.translators.openai.model'),
            'messages' => [
              [
                  'role' => 'system',
                   'content' => $systemPrompt,
              ],
             [   'role' => 'user',
                'content' => $text,
             ]
            ],
        ])->choices[0]->message->content;


        if (! $translation) {
            return '';
        }

        return $translation;
    }

    /**
     * @param  array<string>  $texts Array of texts to translate.
     * @return array<string, string> Array of translated texts.
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $systemPrompt = "You are a professional translator. Your task is to translate multiple text strings from {$sourceLanguage} to {$targetLanguage}.
Instructions:
1. Translate each string from {$sourceLanguage} to {$targetLanguage}.
2. Words prefixed with a colon (:) are special tokens. Do not translate these tokens, keep them as is.
3. Maintain the original structure and formatting of each input string.

Input format: An array of strings in {$sourceLanguage}, potentially containing words prefixed with colons.

Output format: Respond with a single JSON object. Each key-value pair in this object should represent one translation:
- Key: The original string in {$sourceLanguage}
- Value: The translated string in {$targetLanguage}

Example:
For input:
[
    \"Hello :name, how are you?\",
    \"Welcome to our :service!\",
    \"Please contact :support for assistance.\"
]

Output (assuming English to French translation):
{
    \"Hello :name, how are you?\": \"Bonjour :name, comment allez-vous?\",
    \"Welcome to our :service!\": \"Bienvenue dans notre :service !\",
    \"Please contact :support for assistance.\": \"Veuillez contacter :support pour obtenir de l'aide.\"
}

Ensure your entire response is a valid JSON object.";

        $translation = $this->translateClient->chat()->create([
            'model' => config('translator.translators.openai.model'),
            'messages' => [
              [
                  'role' => 'system',
                   'content' => $systemPrompt,
              ],
             [   'role' => 'user',
                'content' => json_encode($texts),
             ]
            ],
            'response_format' => [
                'type' => 'json_object',
            ]
        ])->choices[0]->message->content;


        if (! $translation) {
            return [];
        }

        $translatedKeys = json_decode($translation, true);

        if (! is_array($translatedKeys)) {
            return [];
        }

        return $translatedKeys;
    }

    public function isConfigured(): bool
    {
        /** @var array<string, null|string> $openAiConfig */
        $openAiConfig = config('translator.translators.openai');

        return $openAiConfig['api_key'] && $openAiConfig['model'];
    }
}
