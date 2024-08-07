<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\TranslationManager;
use Illuminate\Console\Command;
use RuntimeException;

class CheckTranslation extends Command
{
    protected $signature = 'translations:check
                            {target : The target language for the translations}
                            {--source : The source language used for the translation provider}
                            {--translate-missing : Translate missing translations using the translation service}
                            {--sort : Sort JSON translation files}';

    protected $description = 'Check and manage translations';


    public function handle(TranslationManager $translationManager): void
    {
        $this->info('Checking translations...');
        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $translateMissing = (bool) $this->option('translate-missing');
        $sort = (bool) $this->option('sort');
        $targetLanguage = is_string($this->argument('target')) ? $this->argument('target') : 'en';
        $sourceFilePaths = config('translator.source_paths');


        $targetJsonPath = config('translator.language_folder') . "/{$targetLanguage}.json";

        $missingTranslations = $translationManager->updateTranslationsFromFile(
            $sourceFilePaths,
            $targetJsonPath,
            $sort,
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
