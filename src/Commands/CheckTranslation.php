<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\TranslationManager;

class CheckTranslation extends BaseTranslationCommand
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

        $options = $this->parseOptions();
        $sourceFilePaths = $this->getSourceFilePaths();
        $targetJsonPath = $this->getTargetJsonPath($options->target);

        $missingTranslations = $translationManager->updateTranslationsFromFile(
            $sourceFilePaths,
            $targetJsonPath,
            $options->sort,
            $options->target,
            $options->translateMissing,
            $options->source
        );

        $this->displayResult($missingTranslations);
    }

    protected function parseOptions(): CommandOptions
    {

        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : 'en',
            target: is_string($this->argument('target')) ? $this->argument('target') : 'en',
            translateMissing: (bool) $this->option('translate-missing'),
            sort: (bool) $this->option('sort')
        );
    }

    /**
     * @param array<string, mixed> $missingTranslations
     */
    private function displayResult(array $missingTranslations): void
    {
        if (empty($missingTranslations)) {
            $this->info('No missing translations found.');
        } else {
            $this->info('Updated translation file with missing translations.');
        }
    }
}
