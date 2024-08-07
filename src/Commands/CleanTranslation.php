<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManager;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;
use Illuminate\Console\Command;
use RuntimeException;

class CleanTranslation extends Command
{
    protected $signature = 'translations:clean
                            {--source : The source language for the translations to clean}
                            {--print : Print the cleaned translations to the console, instead of updating the file}';

    protected $description = 'Clean translations by removing unused keys from the source language file';

    public function handle(): void
    {
        $this->info('Cleaning translations...');

        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $sourceJsonPath = config('translator.language_folder') . "/{$sourceLanguage}.json";
        $translationFinder = new TranslationFinder(new FileManagement(), new LanguageFileManager($sourceJsonPath), new MissingKeysFinder());
        /** @var array<string> $sourceFilePaths */
        $sourceFilePaths = config('translator.source_paths');


        $missingTranslations = $translationFinder->findAllTranslations($sourceFilePaths);
        $sourceFileManager = new LanguageFileManager($sourceJsonPath);
        $sourceTranslations = $sourceFileManager->readFile();

        foreach ($sourceTranslations as $key => $value) {
            if (!in_array($key, $missingTranslations)) {
                unset($sourceTranslations[$key]);
            }
        }

        if ($this->option('print')) {
            foreach ($sourceTranslations as $key => $value) {
                $this->line("Key: {$key}, Value: {$value}");
            }
        } else {
            $sourceFileManager->updateFile($sourceTranslations);
            $this->info('Unused translations removed from the source language file.');
        }
    }
}
