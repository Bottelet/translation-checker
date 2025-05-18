<?php

namespace Bottelet\TranslationChecker\File\Language;

class PhpNestedLanguageFileHelper
{
    //TODO this should be moved to config to avoid possible conflicts
    public const GENERAL_TRANSLATION_FILE_NAME = 'general';

    public static function processNestedKeys(array $translations): array
    {
        $nestedTranslations = [];

        foreach ($translations as $key => $value) {
            if (str_contains($key, '.')) {
                list($fileKey, $remainingKey) = explode('.', $key, 2);

                if (!isset($nestedTranslations[$fileKey])) {
                    $nestedTranslations[$fileKey] = [];
                }

                $nestedTranslations[$fileKey][$remainingKey] = $value;
            } else {
                // Use 'general' for keys without dots
                $nestedTranslations[self::GENERAL_TRANSLATION_FILE_NAME][$key] = $value;
            }
        }

        return $nestedTranslations;
    }

    public static function getAllNestedTranslations(string $language): array
    {
        $languageDir = self::getLocaleDirectory($language);

        // Get all existing translations from nested files
        $existingTranslations = [];
        $phpFiles = glob($languageDir . DIRECTORY_SEPARATOR . '*.php');

        foreach ($phpFiles as $phpFile) {
            $filename = pathinfo($phpFile, PATHINFO_FILENAME);
            $fileTranslations = require $phpFile;

            if (is_array($fileTranslations)) {
                // Convert nested keys to dot notation to match used keys format
                foreach ($fileTranslations as $key => $value) {
                    $fullKey = $filename . '.' . $key;
                    $existingTranslations[$fullKey] = $value;
                }
            }
        }

        return $existingTranslations;
    }

    public static function writeNestedTranslations(array $nestedTranslations, string $language): void
    {
        $languageDir = self::getLocaleDirectory($language);

        // Ensure language directory exists
        if (!file_exists($languageDir)) {
            mkdir($languageDir, 0755, true);
        }

        foreach ($nestedTranslations as $file => $translations) {
            $filePath = $languageDir . DIRECTORY_SEPARATOR . $file . '.php';
            $fileDir = dirname($filePath);

            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0755, true);
            }

            $existingTranslations = [];
            if (file_exists($filePath)) {
                $existingTranslations = require $filePath;
                if (is_array($existingTranslations)) {
                    foreach ($translations as $key => $value) {
                        if (!isset($existingTranslations[$key]) || $value !== null) {
                            $existingTranslations[$key] = $value;
                        }
                    }
                    $translations = $existingTranslations;
                }
            }

            file_put_contents($filePath, "<?php\n\nreturn " . var_export($translations, true) . ";\n");
        }
    }

    public static function cleanNestedTranslations(array $nestedTranslationsToKeep, string $language): void
    {
        $languageDir = self::getLocaleDirectory($language);

        foreach ($nestedTranslationsToKeep as $file => $translations) {
            $filePath = $languageDir . DIRECTORY_SEPARATOR . $file . '.php';
            file_put_contents($filePath, "<?php\n\nreturn " . var_export($translations, true) . ";\n");
        }

        // Delete files that aren't needed anymore
        $filesToKeep = array_map(
            fn ($file) => $languageDir . DIRECTORY_SEPARATOR . $file . '.php',
            array_keys($nestedTranslationsToKeep)
        );

        // Get all existing PHP files in the language directory
        $phpFiles = glob($languageDir . DIRECTORY_SEPARATOR . '*.php');
        foreach ($phpFiles as $phpFile) {
            if (!in_array($phpFile, $filesToKeep)) {
                //delete empty files
                @unlink($phpFile);
            }
        }
    }

    public static function sortLocaleTranslations(string $language)
    {
        $languageDir = self::getLocaleDirectory($language);
        if (!file_exists($languageDir)) {
            return;
        }

        $phpFiles = glob($languageDir . DIRECTORY_SEPARATOR . '*.php');

        foreach ($phpFiles as $file) {
            $fileTranslations = require $file;

            if (!is_array($fileTranslations)) {
                return;
            }

            ksort($fileTranslations);
            file_put_contents($file, "<?php\n\nreturn " . var_export($fileTranslations, true) . ";\n");
        }
    }

    public static function getLangDirectory(): string
    {
        return rtrim(config('translator.language_folder'), DIRECTORY_SEPARATOR);
    }

    public static function getLocaleDirectory(string $locale): string
    {
        return self::getLangDirectory() . DIRECTORY_SEPARATOR . $locale;
    }
}
