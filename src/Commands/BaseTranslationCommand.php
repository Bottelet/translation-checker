<?php

namespace Bottelet\TranslationChecker\Commands;

use Illuminate\Console\Command;
use RuntimeException;

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
            return throw new RuntimeException('The source paths configuration is not an array.');
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
