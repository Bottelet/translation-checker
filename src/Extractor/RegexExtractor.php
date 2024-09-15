<?php

namespace Bottelet\TranslationChecker\Extractor;

use SplFileInfo;

class RegexExtractor implements ExtractorContract
{
    /**
     * @var array<string, string>
     */
    private array $patterns = [
        'doubleUnderscoreSyntax' => '/(__\()([\'"])(.*?)\2/',
        'tSyntax' => '/(?<![\w\$])\$?t\((["\'])(.*?)\1\)/',
        'dollarUnderscorePattern' => '/\$_\([\'"]([^\'"]+)[\'"]\)/',
    ];

    public function extractFromFile(SplFileInfo $file): array
    {
        $found = [];
        $contents = file_get_contents($file->getRealPath());
        if (!$contents) {
            return [];
        }

        if (preg_match_all($this->patterns['doubleUnderscoreSyntax'], $contents, $matches)) {
            $found = array_merge($found, $matches[3]);
        }
        if (preg_match_all($this->patterns['tSyntax'], $contents, $matches)) {
            $found = array_merge($found, $matches[2]);
        }
        if (preg_match_all($this->patterns['dollarUnderscorePattern'], $contents, $matches)) {
            $found = array_merge($found, $matches[1]);
        }

        return $found;
    }
}
