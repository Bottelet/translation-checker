<?php

namespace Bottelet\TranslationChecker\Extractor;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SplFileInfo;

class RegexExtractor implements ExtractorContract
{
    public const DOUBLE_UNDERSCORE_SYNTAX_PATTERN = '/(__\(\s*)([\'"])(.*?)\2/s';
    public const T_SYNTAX_PATTERN = '/(?<![\w$])\$?tc?\(\s*(["\'])(.*?)\1[^)]*?\)/s';
    public const DOLLAR_UNDERSCORE_PATTERN = '/\$_\(\s*[\'"]([^\'"]+)[\'"]\s*\)/s';
    /** @var Collection<int, array{regex: string, matchIndex: int, group: string}> */
    private Collection $patterns;

    public function __construct()
    {
        $this->patterns = collect([
            [
                'regex' => self::DOUBLE_UNDERSCORE_SYNTAX_PATTERN,
                'matchIndex' => 3,
                'group' => 'doubleUnderscoreSyntax',
            ],
            [
                'regex' => self::T_SYNTAX_PATTERN,
                'matchIndex' => 2,
                'group' => 'tSyntax',
            ],
            [
                'regex' => self::DOLLAR_UNDERSCORE_PATTERN,
                'matchIndex' => 1,
                'group' => 'dollarUnderscorePattern',
            ],
        ]);
    }

    public function addPattern(string $regex, int $matchIndex, ?string $group = null): self
    {
        $this->patterns->push([
            'regex' => $regex,
            'matchIndex' => $matchIndex,
            'group' => $group ?? Str::random(10),
        ]);

        return $this;
    }

    public function extractFromFile(SplFileInfo $file): array
    {
        $contents = file_get_contents($file->getRealPath());
        if (!$contents) {
            return [];
        }

        $found = [];

        /** @var array{regex: string, matchIndex: int, group: string}  $pattern */
        foreach ($this->patterns as $pattern) {
            if (preg_match_all($pattern['regex'], $contents, $matches)) {
                $found = array_merge($found, $matches[$pattern['matchIndex']]);
            }
        }

        return $found;
    }
}
