<?php

namespace Bottelet\TranslationChecker\Tests\Translator;

use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Translate\V2\TranslateClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class GoogleTranslatorTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    /** @var VariableRegexHandler|MockObject */
    private $variableHandlerMock;

    /** @var TranslateClient|MockObject */
    private $translateClientMock;

    /** @var GoogleTranslator */
    private $googleTranslator;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the VariableRegexHandler
        $this->variableHandlerMock = $this->createMock(VariableRegexHandler::class);
        // Mock the TranslateClient
        $this->translateClientMock = $this->createMock(TranslateClient::class);

        // Instantiate GoogleTranslator with mocked dependencies
        $this->googleTranslator = new GoogleTranslator($this->variableHandlerMock);

        // Injecting the TranslateClient mock directly as it's a private property
        $reflection = new ReflectionClass(GoogleTranslator::class);
        $translateClientProperty = $reflection->getProperty('translateClient');
        $translateClientProperty->setAccessible(true);
        $translateClientProperty->setValue($this->googleTranslator, $this->translateClientMock);
    }

    #[Test]
    public function translate(): void
    {
        $text = 'Hello, world!';
        $translatedText = 'Bonjour le monde!';
        $targetLanguage = 'fr';

        $this->variableHandlerMock->method('replacePlaceholders')
            ->willReturn($text);

        $this->variableHandlerMock->method('restorePlaceholders')
            ->willReturn($translatedText);

        $this->translateClientMock->method('translate')
            ->willReturn(['text' => $translatedText]);

        $result = $this->googleTranslator->translate($text, $targetLanguage);

        $this->assertSame($translatedText, $result);
    }

    #[Test]
    public function translateBatch(): void
    {
        $texts = ['Hello, world!', 'Good morning'];
        $translatedTexts = ['Bonjour le monde!', 'Bonjour'];
        $targetLanguage = 'fr';

        $translations = array_map(fn ($text) => ['text' => $text], $translatedTexts);

        $this->variableHandlerMock->expects($this->exactly(count($texts)))
            ->method('replacePlaceholders')
            ->willReturnOnConsecutiveCalls(...$texts);

        $this->variableHandlerMock->expects($this->exactly(count($texts)))
            ->method('restorePlaceholders')
            ->willReturnOnConsecutiveCalls(...$translatedTexts);

        $this->translateClientMock->expects($this->once())
            ->method('translateBatch')
            ->willReturn($translations);

        $result = $this->googleTranslator->translateBatch($texts, $targetLanguage);

        $this->assertSame($translatedTexts, $result);
    }
}
