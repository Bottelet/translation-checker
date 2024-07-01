<?php

namespace Bottelet\TranslationChecker\Translator\VariableHandlers;

class VariableRegexHandler implements VariableHandlerContract
{
    private const PLACEHOLDER_REGEX = '/:(\w+)/';

    private array $placeholders = [];

    /**
     * Replace placeholders in the string with unique identifiers.
     */
    public function replacePlaceholders(string $text): string
    {
        return preg_replace_callback(self::PLACEHOLDER_REGEX, function ($matches) {
            $placeholder = $matches[0];
            $index = count($this->placeholders) + 1;
            $varName = "VAR_{$index}";
            $this->placeholders[$varName] = $placeholder;

            return $varName;
        }, $text);
    }

    /**
     * Restore placeholders in the translated text back to their original values.
     */
    public function restorePlaceholders(string $translatedText): string
    {
        foreach ($this->placeholders as $varName => $placeholder) {
            $translatedText = str_replace($varName, $placeholder, $translatedText);
        }

        return $translatedText;
    }
}
