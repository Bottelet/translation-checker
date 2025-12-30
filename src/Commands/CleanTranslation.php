<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;

class CleanTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:clean
                            {--source= : The source language for the translations to clean (defaults to app.locale)}
                            {--print : Print the cleaned translations to the console, instead of updating the file}
                            {--all : Clean all language files (default when no source specified)}';

    protected $description = 'Clean translations by removing unused keys from the source language file';

    public function handle(LanguageDirectoryManager $directoryManager): void
    {
        $this->info('Cleaning translations...');

        $options = $this->parseOptions();
        $cleanAll = $this->option('all') || empty($this->option('source'));
        $sourceFilePaths = $this->getSourceFilePaths();

        if ($cleanAll) {
            $files = $directoryManager->getLanguageFiles();

            foreach ($files as $file) {
                $this->cleanFile($file->getPathname(), $sourceFilePaths, $options->print);
            }
        } else {
            $sourceJsonPath = $this->getTargetLanguagePath($options->source);
            $this->cleanFile($sourceJsonPath, $sourceFilePaths, $options->print);
        }
    }

    /**
     * @param array<string> $sourceFilePaths
     */
    private function cleanFile(string $languageFilePath, array $sourceFilePaths, bool $print): void
    {
        $translationFinder = new TranslationFinder(
            new FileManagement(),
            new LanguageFileManagerFactory($languageFilePath),
            new MissingKeysFinder()
        );

        $missingTranslations = $translationFinder->findAllTranslations($sourceFilePaths)->getKeys();
        $sourceFileManager = new LanguageFileManagerFactory($languageFilePath);
        $sourceTranslations = $sourceFileManager->readFile();

        $cleanedTranslations = array_intersect_key($sourceTranslations, $missingTranslations);
        if ($print) {
            $this->info("File: {$languageFilePath}");
            $this->printTranslations($cleanedTranslations);
        } else {
            $sourceFileManager->updateFile($cleanedTranslations);
            $this->info("Unused translations removed from {$languageFilePath}");
        }
    }

    protected function parseOptions(): CommandOptions
    {
        $defaultLocale = config('app.locale', 'en');

        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : $defaultLocale,
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
