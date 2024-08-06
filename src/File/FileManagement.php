<?php

namespace Bottelet\TranslationChecker;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileManagement
{
    /**
     * @param  array<string>  $paths
     * @return array<SplFileInfo>
     */
    public function getAllFiles(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            if (! file_exists($path)) {
                continue;
            }

            $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($iterator as $file) {
                if ($file instanceof SplFileInfo && $file->isFile()) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}
