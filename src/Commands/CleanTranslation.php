<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;

class CleanTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:clean
                            {--source= : The source language for the translations to clean}
                            {--print : Print the cleaned translations to the console, instead of updating the file}';

    protected $description = 'Clean translations by removing unused keys from the source language file';

    public function handle(): void
    {
        $this->info('Cleaning translations...');

        $options = $this->parseOptions();
        $sourceJsonPath = $this->getTargetLanguagePath($options->source);
        $sourceFilePaths = $this->getSourceFilePaths();

        $translationFinder = new TranslationFinder(
            new FileManagement(),
            new LanguageFileManagerFactory($sourceJsonPath),
            new MissingKeysFinder()
        );

        $missingTranslations = $translationFinder->findAllTranslations($sourceFilePaths);
        $sourceFileManager = new LanguageFileManagerFactory($sourceJsonPath);
        $sourceTranslations = $sourceFileManager->readFile();

        $cleanedTranslations = array_intersect_key($sourceTranslations, array_flip($missingTranslations));
        if ($options->print) {
            $this->printTranslations($cleanedTranslations);
        } else {
            $sourceFileManager->updateFile($cleanedTranslations);
            $this->info('Unused translations removed from the source language file.');
        }
    }

    protected function parseOptions(): CommandOptions
    {
        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : 'en',
            print: (bool) $this->option('print')
        );
    }

    /**
     * @param array<string, string> $translations
     */
    private function printTranslations(array $translations): void
    {
        foreach ($translations as $key => $value) {
            $this->line("Key: {$key}, Value: {$value}");
        }
    }
}
