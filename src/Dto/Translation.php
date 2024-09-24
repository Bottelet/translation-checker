<?php

namespace Bottelet\TranslationChecker\Dto;

class Translation
{
    public function __construct(protected string $key, protected string $path)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
