<?php

namespace Bottelet\TranslationChecker\Dto;

use SplFileInfo;

class MissingTranslation
{
    private bool $exists;

    public function __construct(protected ?string $value, protected ?string $path = null)
    {
        $this->exists = false;
    }


    public function getValue(): string
    {
        return $this->value;
    }
}