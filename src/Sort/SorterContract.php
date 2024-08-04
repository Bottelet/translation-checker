<?php

namespace Bottelet\TranslationChecker\Sort;

interface SorterContract
{
    public function sortByKey(array $strings): array;
}