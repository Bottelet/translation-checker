<?php

namespace Bottelet\TranslationChecker\Sort;

use Bottelet\TranslationChecker\LanguageFileManager;

class AlphabeticSort implements SorterContract
{

    /**
     * @param  array<string, string>  $strings
     *
     * @return array<string, string>
     */
    public function sortByKey(array $strings):array
    {
        ksort($strings);
        return $strings;
    }
}