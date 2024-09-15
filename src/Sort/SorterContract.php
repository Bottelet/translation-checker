<?php

namespace Bottelet\TranslationChecker\Sort;

interface SorterContract
{
    /**
     * @param  array<string, string>  $strings
     *
     * @return array<string, string>
     */
    public function sortByKey(array $strings): array;
}
