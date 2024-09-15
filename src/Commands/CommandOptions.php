<?php

namespace Bottelet\TranslationChecker\Commands;

final class CommandOptions
{
    public function __construct(
        public readonly string $source = 'en',
        public readonly string $target = '',
        public readonly bool $translateMissing = false,
        public readonly bool $sort = false,
        public readonly bool $print = false,
        public readonly bool $all = false
    ) {
    }
}
