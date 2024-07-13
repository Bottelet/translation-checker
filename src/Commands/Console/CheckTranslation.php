<?php

namespace Bottelet\TranslationChecker\Commands\Console;

use Bottelet\TranslationChecker\TranslationManager;
use Illuminate\Console\Command;
use RuntimeException;

class CheckTranslation extends Command
{
    protected $signature = 'check:translations
                            {target : The target language for the translations}
                            {--S|source=en : The source language of your views}
                            {--translate-missing : Translate missing translations using the translation service}
                            {--sort : Sort JSON translation files}';

    protected $description = 'Check and manage translations';

    public function __construct(protected TranslationManager $translationManager)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Checking translations...');
        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $targetLanguage = is_string($this->argument('target')) ? $this->argument('target') : null;

        if (is_null($targetLanguage)) {
            throw new RuntimeException('Target language need to bet set');
        }

        $translateMissing = (bool) $this->option('translate-missing');

        $sourceFilePaths = config('translator.source_paths');
        if (! is_array($sourceFilePaths)) {
            throw new RuntimeException('Source paths needs to be set as array');
        }
        $targetJsonPath = base_path(config('translator.language_folder') . "/{$targetLanguage}.json");

        $missingTranslations = $this->translationManager->updateTranslationsFromFile(
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
