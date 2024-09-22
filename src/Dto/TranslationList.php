<?php

namespace Bottelet\TranslationChecker\Dto;

class TranslationList
{
    public function __construct(protected array $translations = [],)
    {
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function addTranslation(Translation $translation): self
    {
        $this->translations[] = $translation;
        return $this;
    }

    public function getValues(): array
    {
        return array_fill_keys(
            array_map(function (Translation $translation) {
                return $translation->getValue();
            }, $this->translations),
            null
        );
    }
}