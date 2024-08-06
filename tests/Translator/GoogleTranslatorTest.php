<?php

namespace Bottelet\TranslationChecker\Tests\Translator;

use Bottelet\TranslationChecker\TranslationCheckerServiceProvider;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\Facades\Config;
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
        $this->translateClientMock = $this->createMock(TranslateClient::class);
        $this->variableHandlerMock = $this->createMock(VariableRegexHandler::class);
        $this->googleTranslator = new GoogleTranslator($this->variableHandlerMock, $this->translateClientMock);
    }

    #[Test]
    public function googleTranslateIfTextKeyNotReturned(): void
    {
        $this->translateClientMock->method('translate')
                            ->willReturn(['']);
        $this->variableHandlerMock->method('restorePlaceholders')->willReturn('Translated text');

        $result = $this->googleTranslator->translate('Hello', 'fr', 'en');

        $this->assertEquals('', $result);
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

        $this->assertSame(['Hello, world!' => 'Bonjour le monde!', 'Good morning' => 'Bonjour'], $result);
    }

    #[Test]
    public function testGoogleTranslatorBinding(): void
    {
        $googleTranslator = app(GoogleTranslator::class);
        $this->assertInstanceOf(GoogleTranslator::class, $googleTranslator);
    }

}
