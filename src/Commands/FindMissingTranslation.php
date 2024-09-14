<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManager;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;
use Illuminate\Console\Command;
use RuntimeException;

class FindMissingTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:find-missing
                            {--source : The source language for the translations to find}
                            {--print : Print the missing translation to the console, instead of writing to file}';

    protected $description = 'Find missing translations and add the key to the given source language file, with an empty value';


    public function handle(): void
    {
        $this->info('Finding translations...');

        $options = $this->parseOptions();
        $sourceJsonPath = $this->getTargetJsonPath($options->source);
        $sourceFilePaths = $this->getSourceFilePaths();

        $translationFinder = new TranslationFinder(
            new FileManagement(),
            new LanguageFileManager($sourceJsonPath),
            new MissingKeysFinder()
        );

        $missingTranslations = $translationFinder->findMissingTranslations($sourceFilePaths);

        if ($options->print) {
            $this->printMissingTranslations($missingTranslations);
        } else {
            $translationFinder->getLanguageFilerManager()->updateFile($missingTranslations);
            $this->info('Missing translations added to the source language file.');
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
     * @param array<string, string> $missingTranslations
     */
    private function printMissingTranslations(array $missingTranslations): void
    {
        foreach ($missingTranslations as $key => $value) {
            $this->line("$key: $value");
        }
    }
}
