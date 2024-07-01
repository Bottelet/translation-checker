<?php

namespace Bottelet\TranslationChecker;

class JsonTranslationFileManager
{
    /**
     * Reads a JSON translation file and returns its content as an associative array.
     *
     * @return array<string, mixed>
     */
    public function readJsonFile(string $filePath): array
    {
        if (! file_exists($filePath) || ($jsonContent = file_get_contents($filePath)) === false) {
            return [];
        }

        $decodedJson = json_decode($jsonContent, true);

        return is_array($decodedJson) ? $decodedJson : [];
    }

    /**
     * Updates the given JSON file with the provided translations array.
     *
     * @param  array<string, mixed>  $translations
     */
    public function updateJsonFile(string $filePath, array $translations): void
    {
        $jsonContent = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $jsonContent);
    }

    /**
     * Sorts the translations in a JSON file alphabetically by key.
     *
     * @param  string  $filePath  Path to the JSON file.
     */
    public function sortJsonFile(string $filePath): void
    {
        $translations = $this->readJsonFile($filePath);
        ksort($translations, SORT_FLAG_CASE | SORT_NATURAL);
        $this->updateJsonFile($filePath, $translations);
    }
}
