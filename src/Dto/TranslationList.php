<?php

namespace Bottelet\TranslationChecker\Dto;

class TranslationList
{
    /**
     * @param  array<Translation> $translations
     */
    public function __construct(protected array $translations = [])
    {
    }

    /**
     * @return array<Translation>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function addTranslation(Translation $translation): self
    {
        $this->translations[] = $translation;
        return $this;
    }

    /**
     * @return array<string, string|null>
     */
    public function getKeys(): array
    {
        return array_fill_keys(
            array_map(function (Translation $translation) {
                return $translation->getKey();
            }, $this->translations),
            null
        );
    }
}