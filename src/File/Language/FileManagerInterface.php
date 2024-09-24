<?php

namespace Bottelet\TranslationChecker\File\Language;

interface FileManagerInterface
{
    public function __construct(string $filePath);

    /**
     * @return array<string, string>
     */
    public function readFile(): array;

    /**
     * @param array<string, mixed> $translations
    */
    public function updateFile(array $translations): void;

    public function sortFile(): void;

    public function syncFile(FileManagerInterface $targetFile): void;
}
