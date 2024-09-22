<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;
use Bottelet\TranslationChecker\Sort\SorterContract;

class SortTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:sort
                            {--source= : The source language for the translations to sort}
                            {--all : All files found in the config language_folder }';

    protected $description = 'Sort translation file by using the sorter given in config';

    public function __construct(private readonly SorterContract $sorter, private readonly LanguageDirectoryManager $languageDirectoryManager)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Sorting translations...');

        $options = $this->parseOptions();

        if ($options->all) {
            $this->sortAllFiles();
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
            all: (bool) $this->option('all')
        );
    }

    private function sortAllFiles(): void
    {
        $languageFiles = $this->languageDirectoryManager->getLanguageFiles();
        foreach ($languageFiles as $file) {
            $this->sortFile($file);
        }
    }

    private function sortFile(string $filePath): void
    {
        $languageFile = new LanguageFileManagerFactory($filePath);
        $contents = $languageFile->readFile();
        $sortedContents = $this->sorter->sortByKey($contents);
        $languageFile->updateFile($sortedContents);
        $this->info("Sorted: $filePath");
    }
}
