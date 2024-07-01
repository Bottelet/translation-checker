<?php

namespace Bottelet\TranslationChecker\Translator;

interface TranslatorContract
{
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string;

    public function translateBatch(array $text, string $targetLanguage, string $sourceLanguage = 'en'): array;
}
