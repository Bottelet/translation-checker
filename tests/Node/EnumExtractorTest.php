<?php

namespace Bottelet\TranslationChecker\Tests\Node;

use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EnumExtractorTest extends TestCase
{
    #[Test]
    public function canFindTranslationAfterGetInAbstractClassMethod(): void
    {
        $code = <<<'CODE'
        
        <?php
        
        class Test
        {
            public function index()
            {
                __(Enum::LONGER_TEXT->label());
                __(Enum::SHORT->name);
                __(Enum::SHORT->value);
            }
        }
        CODE;

        $file = $this->createTempFile('enum.php', $code);
        $extractor = new PhpClassExtractor;
        $result = $extractor->extractFromFile($file);
        $this->assertContains('Longer text', $result);
        $this->assertContains('SHORT', $result);
        $this->assertContains('short', $result);
        $this->assertCount(3, $result);
    }

    #[Test]
    public function canHandleEnumUsageInArrays(): void
    {
        $code = <<<'CODE'
        <?php
        class Test
        {
            public function index()
            {
                $translations = [
                    __(StatusEnum::PENDING->label()),
                    __(TypeEnum::SYSTEM->name),
                    __(RoleEnum::USER->value),
                ];
            }
        }
        CODE;

        $file = $this->createTempFile('enum_in_arrays.php', $code);
        $extractor = new PhpClassExtractor;
        $result = $extractor->extractFromFile($file);
        $this->assertContains('Pending', $result);
        $this->assertContains('SYSTEM', $result);
        $this->assertContains('user', $result);
        $this->assertCount(3, $result);
    }
}

