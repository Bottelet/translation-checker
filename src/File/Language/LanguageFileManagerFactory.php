<?php

namespace Bottelet\TranslationChecker\File\Language;

class LanguageFileManagerFactory implements FileManagerInterface
{
    protected FileManagerInterface $fileManager;

    public function __construct(protected string $filePath)
    {
        if (str_ends_with($filePath, '.php')) {
            $this->fileManager = new PhpLanguageFileManager($filePath);
        } else {
            $this->fileManager = new JsonLanguageFileManager($filePath);
        }
    }

    /**
     * Reads a JSON translation file and returns its content as an associative array.
     *
     * @return array<string, string>
     */
    public function readFile(): array
    {
        return $this->fileManager->readFile();
    }

    /**
     * Updates the given JSON file with the provided translations array.
     *
     * @param  array<string, mixed>  $translations
     */
    public function updateFile(array $translations): void
    {
        $this->fileManager->updateFile($translations);
    }

    public function sortFile(): void
    {
        $this->fileManager->sortFile();
    }

    public function syncFile(FileManagerInterface $targetFile): void
    {
        $this->fileManager->syncFile($targetFile);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
