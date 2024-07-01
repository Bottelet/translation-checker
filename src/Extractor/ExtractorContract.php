<?php

namespace Bottelet\TranslationChecker\Extractor;

use SplFileInfo;

interface ExtractorContract
{
    /** @return array <string>     */
    public function extractFromFile(SplFileInfo $file): array;
}
