<?php

namespace Bottelet\TranslationChecker\Translator;

interface TranslatorContract
{
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string;

    /**
     * @param  array<string>  $texts
     * @return array<string>
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array;
}
