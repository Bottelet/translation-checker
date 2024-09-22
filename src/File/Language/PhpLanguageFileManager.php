<?php

namespace Bottelet\TranslationChecker\File\Language;

use RuntimeException;

class PhpLanguageFileManager implements FileManagerInterface
{
    public function __construct(protected string $filePath)
    {
    }

    /**
     * Reads a PHP translation file and returns its content as an associative array.
     *
     * @return array<string, string>
     */
    public function readFile(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = require $this->filePath;

        if (!is_array($content)) {
            throw new RuntimeException('PHP file does not return an array');
        }

        return $content;
    }

    /**
     * Updates the given PHP file with the provided translations array.
     *
     * @param array<string, mixed> $translations
     */
    public function updateFile(array $translations): void
    {
        $formattedTranslations = [];
        foreach ($translations as $key => $value) {
            $this->setNestedValue($formattedTranslations, explode('.', $key), $key);
        }

        $content = "<?php\n\nreturn " . var_export($formattedTranslations, true) . ";\n";
        file_put_contents($this->filePath, $content);
    }

    /**
     * Recursively sets a nested value in an array.
     *
     * @param array<string, mixed> &$array
     * @param array<int, string> $keys
     * @param mixed $value
     */
    private function setNestedValue(array &$array, array $keys, $value): void
    {
        $key = array_shift($keys);
        if (empty($keys)) {
            $array[$key] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $this->setNestedValue($array[$key], $keys, $value);
        }
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