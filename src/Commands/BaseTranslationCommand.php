<?php

namespace Bottelet\TranslationChecker\Commands;

use Illuminate\Console\Command;

abstract class BaseTranslationCommand extends Command
{
    abstract protected function parseOptions(): CommandOptions;

    /**
     * @return array<string>
     */
    protected function getSourceFilePaths(): array
    {
        $paths = config('translator.source_paths', []);

        if (!is_array($paths)) {
            return [];
        }

        return array_filter(array_map(function ($path) {
            return is_string($path) ? $path : '';
        }, $paths));
    }

    protected function getTargetJsonPath(string $targetLanguage): string
    {
        return config('translator.language_folder') . "/{$targetLanguage}.json";
    }
}