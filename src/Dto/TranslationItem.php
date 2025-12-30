<?php

namespace Bottelet\TranslationChecker\Dto;

class TranslationItem
{
    public function __construct(
        protected string $key,
        protected ?string $path = null
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
