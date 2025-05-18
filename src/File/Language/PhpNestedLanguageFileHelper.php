<?php

namespace Bottelet\TranslationChecker\File\Language;

class PhpNestedLanguageFileHelper
{
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
                if (!isset($nestedTranslations['general'])) {
                    $nestedTranslations['general'] = [];
                }
                $nestedTranslations['general'][$key] = $value;
            }
        }

        return $nestedTranslations;
    }

    public static function writeNestedTranslations(array $nestedTranslations, string $languageFolder, string $language): void
    {
        $languageDir = rtrim($languageFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $language;
        if (!file_exists($languageDir)) {
            mkdir($languageDir, 0755, true);
        }

        foreach ($nestedTranslations as $file => $translations) {
            if (empty($translations)) {
                continue;
            }

            $filePath = $languageDir . DIRECTORY_SEPARATOR . $file . '.php';

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
}
