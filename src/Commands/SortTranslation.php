<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\File\Language\PhpNestedLanguageFileHelper;
use Bottelet\TranslationChecker\Sort\SorterContract;

class SortTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:sort
                            {--source= : The source language for the translations to sort}
                            {--all : All files found in the config language_folder }
                            {--nested : Use nested structure where keys are split at the first dot}';

    protected $description = 'Sort translation file by using the sorter given in config';

    public function __construct(private readonly SorterContract $sorter, private readonly LanguageDirectoryManager $languageDirectoryManager)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Sorting translations...');

        $options = $this->parseOptions();
        $nested = $options->nested;

        if ($nested) {
            $this->processNestedStructure($options->source, $options->all);
            $this->info('Translation sorting completed.');

            return;
        }

        if ($options->all) {
            $languageFiles = $this->languageDirectoryManager->getLanguageFiles();
            foreach ($languageFiles as $file) {
                $this->sortFile($file->getPathname());
            }
        } else {
            $targetJsonPath = $this->getTargetLanguagePath($options->source);
            $this->sortFile($targetJsonPath);
        }

        $this->info('Translation sorting completed.');
    }

    protected function parseOptions(): CommandOptions
    {
        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : 'en',
            all: (bool) $this->option('all'),
            nested: (bool) $this->option('nested')
        );
    }

    private function sortFile(string $filePath): void
    {
        $languageFile = new LanguageFileManagerFactory($filePath);
        $contents = $languageFile->readFile();
        $sortedContents = $this->sorter->sortByKey($contents);
        $languageFile->updateFile($sortedContents);
        $this->info("Sorted: $filePath");
    }

    private function processNestedStructure(string $language, bool $all = false): void
    {
        $langDir = PhpNestedLanguageFileHelper::getLangDirectory();

        if ($all) {
            foreach (scandir($langDir) as $locale) {
                if (!in_array($locale, ['.', '..']) && is_dir(PhpNestedLanguageFileHelper::getLocaleDirectory($locale))) {
                    PhpNestedLanguageFileHelper::sortLocaleTranslations($locale);
                    $this->info("Sorted nested PHP file: {$locale}.php");
                }
            }
            return;
        }

        PhpNestedLanguageFileHelper::sortLocaleTranslations($language);
        $this->info("Sorted nested PHP file: {$language}.php");
    }
}
