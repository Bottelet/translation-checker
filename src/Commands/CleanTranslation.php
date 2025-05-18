<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\File\Language\PhpNestedLanguageFileHelper;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;

class CleanTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:clean
                            {--source= : The source language for the translations to clean}
                            {--print : Print the cleaned translations to the console, instead of updating the file}
                            {--nested : Use nested structure where keys are split at the first dot}';

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

        // Get all used keys from source files
        $usedTranslations = $translationFinder->findAllTranslations($sourceFilePaths)->getKeys();

        if ($options->nested) {
            $this->cleanNestedTranslations($usedTranslations, $options->source, $options->print);
        } else {
            $this->cleanSingleFileTranslations($usedTranslations, $sourceJsonPath, $options->print);
        }
    }

    protected function cleanSingleFileTranslations(array $usedTranslations, string $sourceJsonPath, bool $print = true): void
    {
        $sourceFileManager = new LanguageFileManagerFactory($sourceJsonPath);
        $sourceTranslations = $sourceFileManager->readFile();

        $cleanedTranslations = array_intersect_key($sourceTranslations, $usedTranslations);

        if ($print) {
            $this->printTranslations($cleanedTranslations);
        } else {
            $sourceFileManager->updateFile($cleanedTranslations);
        }
    }

    protected function cleanNestedTranslations(array $usedTranslations, string $language, bool $print = true): void
    {
        $existingTranslations = PhpNestedLanguageFileHelper::getAllNestedTranslations($language);

        $kept = array_intersect_key($existingTranslations, $usedTranslations);
        $removed = array_diff_key($existingTranslations, $usedTranslations);

        if ($print) {
            $this->printTranslations($kept, 'Kept');
            $this->printTranslations($removed, 'Removed');

            return;
        }

        $translationsToKeep = PhpNestedLanguageFileHelper::processNestedKeys($kept);

        PhpNestedLanguageFileHelper::cleanNestedTranslations(
            $translationsToKeep,
            $language
        );

        $this->printTranslations($removed, 'Removed');
    }

    protected function parseOptions(): CommandOptions
    {
        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : 'en',
            print: (bool) $this->option('print'),
            nested: (bool) $this->option('nested')
        );
    }

    /**
     * @param array<string, string> $translations
     */
    private function printTranslations(array $translations, string $message = ''): void
    {
        $this->info("\n=== $message (" . count($translations) . ') ===');
        foreach ($translations as $key => $value) {
            $this->line("$key: " . (is_array($value) ? json_encode($value) : $value));
        }
    }
}
