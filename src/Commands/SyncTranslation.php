<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManager;
use Illuminate\Console\Command;

class SyncTranslation extends Command
{
    protected $signature = 'translations:sync
                            {--source= : The source language file to sync from}
                            {--target : The target language file to sync to}';

    protected $description = 'Sync translations between language files';

    public function handle(LanguageDirectoryManager $directoryManager): void
    {
        $source = $this->option('source');
        $target = $this->option('target');
        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $sourcePath = config('translator.language_folder') . "/{$sourceLanguage}.json";

        $sourceFileManager = new LanguageFileManager($sourcePath);

        if ($target) {
            $target = is_string($this->option('target')) ? $this->option('target') : 'en';
            $targetPath = config('translator.language_folder') . "/{$target}.json";

            $targetFileManager = new LanguageFileManager($targetPath);
            $sourceFileManager->syncFile($targetFileManager);
        } else {
            $files = $directoryManager->getLanguageFiles();

            foreach ($files as $file) {
                $targetFileManager = new LanguageFileManager($file->getPathname());

                if ($file->getPathname() !== $source) {
                    $sourceFileManager->syncFile($targetFileManager);
                }
            }
        }
    }
}
