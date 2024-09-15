<?php

namespace Bottelet\TranslationChecker\Sort;

class AlphabeticSort implements SorterContract
{
    /**
     * @param  array<string, string>  $strings
     *
     * @return array<string, string>
     */
    public function sortByKey(array $strings): array
    {
        ksort($strings);

        return $strings;
    }
}
