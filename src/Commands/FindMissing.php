<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\FileManagement;
use Bottelet\TranslationChecker\LanguageFileManager;
use Bottelet\TranslationChecker\MissingKeysFinder;
use Bottelet\TranslationChecker\TranslationFinder;
use Bottelet\TranslationChecker\TranslationManager;
use Illuminate\Console\Command;
use RuntimeException;

class FindMissing extends Command
{
    protected $signature = 'translations:find-missing
                            {--source : The source language for the translations to find}
                            {--print-missing : Print the missing translation to the console, instead of writing to file}';

    protected $description = 'Check and manage translations';


    public function handle(): void
    {
        $this->info('Finding translations...');

        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $targetJsonPath = config('translator.language_folder') . "/{$sourceLanguage}.json";

        $translationFinder = new TranslationFinder(new FileManagement(), new LanguageFileManager($targetJsonPath), new MissingKeysFinder());

        $sourceFilePaths = config('translator.source_paths');
        if (! is_array($sourceFilePaths)) {
            throw new RuntimeException('Source paths needs to be set as array');
        }
        $missingTranslations = $translationFinder->findMissingTranslations($sourceFilePaths);

        if ($this->option('print-missing')) {
            //$this->table(['translations'], $missingTranslations);
        } else {
            $translationFinder->getLanguageFilerManager()->updateJsonFile($missingTranslations);
        }
    }
}
