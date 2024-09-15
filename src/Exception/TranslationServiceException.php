<?php

namespace Bottelet\TranslationChecker\Exception;

use Exception;

class TranslationServiceException extends Exception
{
    public static function notConfigured(string $translationServiceName): self
    {
        return new self("Translation service {$translationServiceName} is not configured. add correct environment variables or configure the service.");
    }
}
