<?php

namespace Bottelet\TranslationChecker\File\Language;

use RuntimeException;

class JsonLanguageFileManager implements FileManagerInterface
{
    public function __construct(protected string $filePath)
    {
    }

    /**
     * Reads a JSON translation file and returns its content as an associative array.
     *
     * @return array<string, string>
     */
    public function readFile(): array
    {
        if (! file_exists($this->filePath) || ($jsonContent = file_get_contents($this->filePath)) === false) {
            return [];
        }

        $decodedJson = json_decode($jsonContent, true);

        if (!is_array($decodedJson)) {
            throw new RuntimeException('Could not parse language file:' . json_last_error_msg());
        }

        return $decodedJson;
    }

    /**
     * Updates the given JSON file with the provided translations array.
     *
     * @param  array<string, mixed>  $translations
     */
    public function updateFile(array $translations): void
    {
        $jsonContent = json_encode($translations, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->filePath, $jsonContent);
    }

    public function sortFile(): void
    {
        $translations = $this->readFile();
        ksort($translations, SORT_FLAG_CASE | SORT_NATURAL);
        $this->updateFile($translations);
    }

    public function syncFile(FileManagerInterface $targetFile): void
    {
        $sourceTranslations = $this->readFile();
        $targetTranslations = $targetFile->readFile();

        foreach ($sourceTranslations as $key => $value) {
            if (!array_key_exists($key, $targetTranslations)) {
                $targetTranslations[$key] = $value;
            }
        }

        $targetFile->updateFile($targetTranslations);
    }
}
