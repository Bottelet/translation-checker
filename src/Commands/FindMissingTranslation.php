<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\FileManagement;
use Bottelet\TranslationChecker\File\Language\LanguageFileManager;
use Bottelet\TranslationChecker\Finder\MissingKeysFinder;
use Bottelet\TranslationChecker\Finder\TranslationFinder;
use Illuminate\Console\Command;
use RuntimeException;

class FindMissingTranslation extends Command
{
    protected $signature = 'translations:find-missing
                            {--source : The source language for the translations to find}
                            {--print : Print the missing translation to the console, instead of writing to file}';

    protected $description = 'Find missing translations and add the key to the given source language file, with an empty value';


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

        if ($this->option('print')) {
            foreach ($missingTranslations as $translation) {
                $this->line($translation);
            }
        } else {
            $translationFinder->getLanguageFilerManager()->updateFile($missingTranslations);
        }
    }
}
