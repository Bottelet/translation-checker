<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManager;
use Bottelet\TranslationChecker\Sort\SorterContract;
use Illuminate\Console\Command;

class SortTranslation extends Command
{
    protected $signature = 'translations:sort
                            {--source : The source language for the translations to find}
                            {--all : All files found in the config language_folder }';

    protected $description = 'Sort translation file by using the sorter given in config';

    public function __construct(private SorterContract $sorter, private LanguageDirectoryManager $languageDirectoryManager)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Sorting translations...');

        $sourceLanguage = is_string($this->option('source')) ? $this->option('source') : 'en';
        $targetJsonPath = config('translator.language_folder') . "/{$sourceLanguage}.json";

        $this->sortFile($targetJsonPath);
    }

    protected function sortFile(string $targetJsonPath): void
    {
        if ($this->option('all')) {
            $languageFiles = $this->languageDirectoryManager->getLanguageFiles();
            foreach ($languageFiles as $file) {
                $this->processFile($file);
            }
        } else {
            $this->processFile($targetJsonPath);
        }
    }

    protected function processFile(string $filePath): void
    {
        $languageFile = new LanguageFileManager($filePath);
        $contents = $languageFile->readFile();
        $sortedContents = $this->sorter->sortByKey($contents);
        $languageFile->updateFile($sortedContents);
    }
}
