<?php

namespace Bottelet\TranslationChecker\Dto;

use SplFileInfo;

class Translation
{
    public function __construct(protected string $value, protected string $path)
    {

    }

    public function getValue(): string
    {
        return $this->value;
    }
}