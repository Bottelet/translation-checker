<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\TranslationManager;
use Illuminate\Console\Command;
use RuntimeException;

class CheckTranslation extends Command
{
    protected $signature = 'translations:check
                            {target : The target language for the translations}
                            {--S|source=en : The source language used for the translation provider}
                            {--translate-missing : Translate missing translations using the translation service}
                            {--sort : Sort JSON translation files}';

    protected $description = 'Check and manage translations';


    public function handle(TranslationManager $translationManager): void
    {
        $this->info('Checking translations...');
        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $translateMissing = (bool) $this->option('translate-missing');
        $targetLanguage = is_string($this->argument('target')) ? $this->argument('target') : null;

        if (is_null($targetLanguage)) {
            throw new RuntimeException('Target language need to bet set');
        }

        $sourceFilePaths = config('translator.source_paths');
        if (! is_array($sourceFilePaths)) {
            throw new RuntimeException('Source paths needs to be set as array');
        }

        $targetJsonPath = base_path(config('translator.language_folder') . "/{$targetLanguage}.json");

        $missingTranslations = $translationManager->updateTranslationsFromFile(
            $sourceFilePaths,
            $targetJsonPath,
            $targetLanguage,
            $translateMissing,
            $sourceLanguage,
        );

        if (empty($missingTranslations)) {
            $this->info('No missing translations found.');
        } else {
            $this->info('Updated translation file with missing translations.');
        }
    }
}
