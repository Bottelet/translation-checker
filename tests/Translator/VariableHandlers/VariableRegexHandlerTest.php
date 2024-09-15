<?php

namespace Bottelet\TranslationChecker\Tests\Translator\VariableHandlers;

use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VariableRegexHandlerTest extends TestCase
{
    #[Test]
    public function replacePlaceholdersWithUniqueValues(): void
    {
        $handler = new VariableRegexHandler;

        $text = 'Test with :key1 and :key2';
        $expectedReplacedText = 'Test with VAR_1 and VAR_2';
        $replacedText = $handler->replacePlaceholders($text);
        $this->assertEquals($expectedReplacedText, $replacedText);
    }

    #[Test]
    public function replacePlaceholdersWithMultipleOccurrences(): void
    {
        $handler = new VariableRegexHandler;

        $text = 'Test with :key1 and :key1';
        $expectedReplacedText = 'Test with VAR_1 and VAR_2';
        $replacedText = $handler->replacePlaceholders($text);
        $this->assertEquals($expectedReplacedText, $replacedText);
    }

    #[Test]
    public function replacePlaceholdersWithAdditionalCharacters(): void
    {
        $handler = new VariableRegexHandler;

        $text = 'Test with: :key1! and ? :key2.';
        $expectedReplacedText = 'Test with: VAR_1! and ? VAR_2.';
        $replacedText = $handler->replacePlaceholders($text);
        $this->assertEquals($expectedReplacedText, $replacedText);
    }

    #[Test]
    public function restorePlaceholdersDifferentVariables(): void
    {
        $handler = new VariableRegexHandler;

        $text = 'Test with :key1 and :key2';
        $replacedText = $handler->replacePlaceholders($text);
        $this->assertEquals('Test with VAR_1 and VAR_2', $replacedText);

        $restoredText = $handler->restorePlaceholders($replacedText);
        $this->assertEquals($text, $restoredText);
    }

    #[Test]
    public function restorePlaceholdersSameVariables(): void
    {
        $handler = new VariableRegexHandler;

        $text = 'Test with :name and :name, and something about the :name';
        $replacedText = $handler->replacePlaceholders($text);
        $this->assertEquals('Test with VAR_1 and VAR_2, and something about the VAR_3', $replacedText);

        $restoredText = $handler->restorePlaceholders($replacedText);
        $this->assertEquals($text, $restoredText);
    }
}
