<?php

namespace Bottelet\TranslationChecker\Dto;

use SplFileInfo;

class MissingTranslation
{
    protected bool $exists;

    public function __construct(protected ?string $key, protected ?string $path = null)
    {
        $this->exists = false;
    }


    public function getKey(): ?string
    {
        return $this->key;
    }
}