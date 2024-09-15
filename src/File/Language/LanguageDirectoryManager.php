<?php

namespace Bottelet\TranslationChecker\File\Language;

use Bottelet\TranslationChecker\File\FileManagement;
use SplFileInfo;

class LanguageDirectoryManager
{
    public function __construct(protected string $directoryPath = "")
    {
        if(!$directoryPath) {
            /** @var string $path */
            $path = config('translator.language_folder',  base_path('/lang'));
            $this->directoryPath = $path;
        }
    }


    /**
     * Get all files within the language directory.
     *
     * @return array<SplFileInfo>
     */
    public function getLanguageFiles(): array
    {
        return (new FileManagement())->getAllFiles([$this->directoryPath]);
    }
}