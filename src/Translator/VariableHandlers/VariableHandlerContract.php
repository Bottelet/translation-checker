<?php

namespace Bottelet\TranslationChecker\Translator\VariableHandlers;

interface VariableHandlerContract
{
    public function replacePlaceholders(string $text): string;

    public function restorePlaceholders(string $translatedText): string;
}
