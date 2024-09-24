<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Illuminate\Console\Command;

class SyncTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:sync
                            {--source= : The source language file to sync from}
                            {--target : The target language file to sync to}';

    protected $description = 'Sync translations between language files';

    public function handle(LanguageDirectoryManager $directoryManager): void
    {
        $options = $this->parseOptions();
        $source = $options->source;
        $target = $options->target;
        $sourcePath = $this->getTargetLanguagePath($source);

        $sourceFileManager = new LanguageFileManagerFactory($sourcePath);

        if ($target) {
            $targetPath = $this->getTargetLanguagePath($target);

            $targetFileManager = new LanguageFileManagerFactory($targetPath);
            $sourceFileManager->syncFile($targetFileManager);
        } else {
            $files = $directoryManager->getLanguageFiles();

            foreach ($files as $file) {
                $targetFileManager = new LanguageFileManagerFactory($file->getPathname());

                if ($file->getPathname() !== $source) {
                    $sourceFileManager->syncFile($targetFileManager);
                }
            }
        }
    }

    protected function parseOptions(): CommandOptions
    {
        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : 'en',
            target: is_string($this->option('target')) ? $this->option('target') : '',
        );
    }
}
