<?php

namespace Bottelet\TranslationChecker\Dto;

class TranslationCollection
{
    /**
     * @param  array<TranslationItem> $translations
     */
    public function __construct(protected array $translations = [])
    {
    }

    /**
     * @return array<TranslationItem>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function addTranslation(TranslationItem $translation): self
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
            array_map(fn (TranslationItem $translation) => $translation->getKey(), $this->translations),
            null
        );
    }
}
