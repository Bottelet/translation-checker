<?php

namespace Bottelet\TranslationChecker\Dto;

class MissingTranslationList
{
    /**
     * @param  array<MissingTranslation> $translations
     */
    public function __construct(protected array $translations = [])
    {
    }

    /**
     * @return array<MissingTranslation>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function addTranslation(MissingTranslation $translation): self
    {
        $this->translations[] = $translation;
        return $this;
    }

    /**
     * @return array<string, string|null>
     */
    public function getValues(): array
    {
        return array_fill_keys(
            array_map(function (MissingTranslation $translation) {
                return $translation->getValue();
            }, $this->translations),
            null
        );
    }
}