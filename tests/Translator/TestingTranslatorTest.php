<?php

namespace Bottelet\TranslationChecker\Tests\Translator;

use Bottelet\TranslationChecker\Tests\TestCase;
use Bottelet\TranslationChecker\Tests\TestingTranslator;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test to ensure that the TestingTranslator keeps returns the same results through all tests
 */
class TestingTranslatorTest extends TestCase
{

    private TestingTranslator $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = new TestingTranslator();
    }

    #[Test]
    public function translateIntoNothing()
    {
        $this->assertEquals('nothing', $this->translator->translate('anything', 'en'));
    }

    #[Test]
    public function translateBatchIntoNothing()
    {
        $this->assertEquals(['nothing'], $this->translator->translateBatch(['anything'], 'nothing'));
    }
}
