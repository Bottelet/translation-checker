<?php

namespace Bottelet\TranslationChecker\Tests\Node;

use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use Bottelet\TranslationChecker\Node\EnumExtractor;
use Bottelet\TranslationChecker\Node\TranslateCommentExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
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

        $result = $this->parseAndExtract($code);
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

        $result = $this->parseAndExtract($code);
        $this->assertContains('Pending', $result);
        $this->assertContains('SYSTEM', $result);
        $this->assertContains('user', $result);
        $this->assertCount(3, $result);
    }

    private function parseAndExtract(string $code): array
    {
        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 2));
        $ast = $parser->parse($code);

        $extractor = new EnumExtractor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($extractor);
        $traverser->traverse($ast);

        return $extractor->getTranslationKeys();
    }

    #[Test]
    public function canHandleComplexEnumUsage(): void
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
                    __($this->getEnum()::CASE->customMethod()),
                ];
            }
            
            private function getEnum()
            {
                return StatusEnum::class;
            }
        }
        CODE;

        $result = $this->parseAndExtract($code);
        $this->assertContains('Pending', $result);
        $this->assertContains('SYSTEM', $result);
        $this->assertContains('user', $result);
        $this->assertNotContains('Case', $result);
        $this->assertCount(3, $result);
    }

    #[Test]
    public function canHandleMethodCallWithVariableName(): void
    {
        $factory = new BuilderFactory;
        $node = $factory->funcCall('__', [
            $factory->methodCall(
                $factory->classConstFetch('SomeEnum', 'SOME_CASE'),
                new Variable('methodName')
            )
        ]);

        $extractor = new EnumExtractor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($extractor);

        // Traverse the AST
        $traverser->traverse([$node]);

        $result = $extractor->getTranslationKeys();


        $this->assertCount(0, $result);
    }
}

