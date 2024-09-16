<?php

namespace Bottelet\TranslationChecker\Tests\Node;

use Bottelet\TranslationChecker\Node\TranslateCommentExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\Attributes\Test;

class TranslateCommentExtractorTest extends TestCase
{
    #[Test]
    public function ExtractSimpleTranslation(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate */
        $logger->log('Simple message');
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('Simple message', $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function IgnoreNonTranslatedLog(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        $logger->log('Non-translated message');
        /** @translate */
        $logger->log('Translated message');
        CODE;

        $result = $this->parseAndExtract($code);
        $this->assertContains('Translated message', $result);
        $this->assertNotContains('Non-translated message', $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function MultipleTranslations(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate */
        $logger->log('First message');
        $logger->log('Non-translated message');
        /** @translate */
        $logger->log('Second message');
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('First message', $result);
        $this->assertContains('Second message', $result);
        $this->assertNotContains('Non-translated message', $result);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function IgnoreOtherMethods(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate */
        $logger->log('Translated message');
        $logger->otherMethod('Should not be translated');
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('Translated message', $result);
        $this->assertNotContains('Should not be translated', $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function HandleDynamicStrings(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        $message = 'Dynamic message';
        /** @translate */
        $logger->log($message);
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertEmpty($result, 'Dynamic strings should not be extracted');
    }

    #[Test]
    public function HandleConcatenatedStrings(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate */
        $logger->log('Concatenated ' . 'message');
        CODE;

        $result = $this->parseAndExtract($code);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function HandleMultilineDocBlock(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /**
         * This is a multiline DocBlock
         * @translate
         * @param string $message
         */
        $logger->log('Multiline DocBlock message');
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('Multiline DocBlock message', $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function IgnoreTranslateCommentInWrongPlace(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        $logger->log('Not translated');
        /** @translate */
        $someOtherOperation();
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertEmpty($result);
    }

    #[Test]
    public function ItCanHandleInputsInTranslateComment(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate<pending, in-progress, done, canceled> */
        $logger->log($status);
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('pending', $result);
        $this->assertContains('in-progress', $result);
        $this->assertContains('done', $result);
        $this->assertContains('canceled', $result);
        $this->assertCount(4, $result);
    }

    #[Test]
    public function canHandleDifferentFormatInputsInTranslateComment(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate<pending,    test   ,   This is quite long text? With special chars!, Well/nice> */
        $logger->log($status);
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('pending', $result);
        $this->assertContains('test', $result);
        $this->assertContains('This is quite long text? With special chars!', $result, );
        $this->assertContains('Well/nice', $result);
        $this->assertCount(4, $result, 'Should extract 4 keys');
    }

    #[Test]
    public function otherComment(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @other */
        $logger->log('Simple message');
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertEmpty($result);
    }

    #[Test]
    public function HandleComplexConcatenatedStrings(): void
    {
        $code = <<<'CODE'
        <?php
        $logger = new Logger();
        /** @translate */
        $logger->log('Part1' . ('Part2' . 'Part3') . ('Part4' . ('Part5' . 'Part6')));
        CODE;

        $result = $this->parseAndExtract($code);

        $this->assertContains('Part1', $result);
        $this->assertContains('Part2', $result);
        $this->assertContains('Part3', $result);
        $this->assertContains('Part4', $result);
        $this->assertContains('Part5', $result);
        $this->assertContains('Part6', $result);
        $this->assertCount(6, $result);
    }

    private function parseAndExtract(string $code): array
    {
        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 2));
        $ast = $parser->parse($code);

        $extractor = new TranslateCommentExtractor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($extractor);

        $traverser->traverse($ast);

        return $extractor->getTranslationKeys();
    }
}
