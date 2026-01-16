<?php

namespace Bottelet\TranslationChecker\Commands;

use Bottelet\TranslationChecker\File\Language\LanguageDirectoryManager;
use Bottelet\TranslationChecker\File\Language\LanguageFileManagerFactory;

class SyncTranslation extends BaseTranslationCommand
{
    protected $signature = 'translations:sync
                            {--source= : The source language file to sync from (defaults to app.locale)}
                            {--target= : The target language file to sync to}
                            {--all : Sync to all language files (default when no target specified)}';

    protected $description = 'Sync translations between language files';

    public function handle(LanguageDirectoryManager $directoryManager): void
    {
        $options = $this->parseOptions();
        $source = $options->source;
        $target = $options->target;
        $syncAll = $this->option('all') || empty($target);
        $languageFolder = config('translator.language_folder');

        if ($syncAll) {
            $targetLanguages = $this->getAvailableLanguages($directoryManager, $source);
            
            foreach ($targetLanguages as $targetLang) {
                $this->syncLanguage($source, $targetLang, $languageFolder);
            }
        } else {
            $this->syncLanguage($source, $target, $languageFolder);
        }
    }

    protected function syncLanguage(string $sourceLanguage, string $targetLanguage, string $languageFolder): void
    {
        $sourceFiles = $this->getLanguageFiles($languageFolder, $sourceLanguage);
        
        foreach ($sourceFiles as $sourceFile) {
            $relativePath = $this->getRelativePath($sourceFile, $languageFolder, $sourceLanguage);
            $targetFilePath = $this->buildTargetPath($languageFolder, $targetLanguage, $relativePath, $sourceLanguage);
            
            if (!file_exists(dirname($targetFilePath))) {
                mkdir(dirname($targetFilePath), 0777, true);
            }
            
            $sourceFileManager = new LanguageFileManagerFactory($sourceFile);
            $targetFileManager = new LanguageFileManagerFactory($targetFilePath);
            
            $sourceFileManager->syncFile($targetFileManager);
        }
    }

    /**
     * @return array<string>
     */
    protected function getLanguageFiles(string $languageFolder, string $language): array
    {
        $files = [];
        
        $jsonFile = "{$languageFolder}/{$language}.json";
        if (file_exists($jsonFile)) {
            $files[] = $jsonFile;
        }
        
        $phpFile = "{$languageFolder}/{$language}.php";
        if (file_exists($phpFile)) {
            $files[] = $phpFile;
        }
        
        $langDir = "{$languageFolder}/{$language}";
        if (is_dir($langDir)) {
            $directoryManager = new LanguageDirectoryManager($langDir);
            foreach ($directoryManager->getLanguageFiles() as $file) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }

    protected function getRelativePath(string $filePath, string $languageFolder, string $language): string
    {
        $basename = basename($filePath);
        
        if ($basename === "{$language}.json" || $basename === "{$language}.php") {
            return $basename;
        }
        
        $langDir = rtrim("{$languageFolder}/{$language}", '/') . '/';
        $relativePath = str_replace($langDir, '', $filePath);
        
        return $relativePath;
    }

    protected function buildTargetPath(string $languageFolder, string $targetLanguage, string $relativePath, string $sourceLanguage): string
    {
        if ($relativePath === "{$sourceLanguage}.json") {
            return "{$languageFolder}/{$targetLanguage}.json";
        }
        
        if ($relativePath === "{$sourceLanguage}.php") {
            return "{$languageFolder}/{$targetLanguage}.php";
        }
        
        return "{$languageFolder}/{$targetLanguage}/{$relativePath}";
    }

    /**
     * @return array<string>
     */
    protected function getAvailableLanguages(LanguageDirectoryManager $directoryManager, string $excludeLanguage): array
    {
        $languages = [];
        $languageFolder = config('translator.language_folder');
        
        $allFiles = $directoryManager->getLanguageFiles();
        foreach ($allFiles as $file) {
            $filePath = $file->getPathname();
            $relativePath = str_replace($languageFolder . '/', '', $filePath);
            
            if (str_ends_with($filePath, '.json') && !str_contains($relativePath, '/')) {
                $lang = basename($filePath, '.json');
                if ($lang !== $excludeLanguage && !in_array($lang, $languages)) {
                    $languages[] = $lang;
                }
            } elseif (str_ends_with($filePath, '.php') && !str_contains($relativePath, '/')) {
                $lang = basename($filePath, '.php');
                if ($lang !== $excludeLanguage && !in_array($lang, $languages)) {
                    $languages[] = $lang;
                }
            } else {
                $parts = explode('/', $relativePath);
                if (count($parts) > 1) {
                    $lang = $parts[0];
                    if ($lang !== $excludeLanguage && !in_array($lang, $languages)) {
                        $languages[] = $lang;
                    }
                }
            }
        }
        
        return $languages;
    }

    protected function parseOptions(): CommandOptions
    {
        return new CommandOptions(
            source: is_string($this->option('source')) ? $this->option('source') : 'en',
            target: is_string($this->option('target')) ? $this->option('target') : '',
        );
    }
}
