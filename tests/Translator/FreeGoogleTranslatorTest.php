<?php

namespace Bottelet\TranslationChecker\Tests\Translator;

use Bottelet\TranslationChecker\Translator\FreeGoogleTranslator;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class FreeGoogleTranslatorTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    /** @var VariableRegexHandler|MockObject */
    private $variableHandlerMock;

    /** @var \Stichoza\GoogleTranslate\GoogleTranslate|MockObject */
    private $translateClientMock;

    /** @var FreeGoogleTranslator */
    private $freeGoogleTranslator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translateClientMock = $this->createMock(\Stichoza\GoogleTranslate\GoogleTranslate::class);
        $this->variableHandlerMock = $this->createMock(VariableRegexHandler::class);
        $this->freeGoogleTranslator = new FreeGoogleTranslator($this->variableHandlerMock, $this->translateClientMock);
    }

    #[Test]
    public function freeGoogleTranslateIfTextKeyNotReturned(): void
    {
        $this->translateClientMock->method('translate')->willReturn(null);

        $this->variableHandlerMock->method('restorePlaceholders')->willReturn('Translated text');

        $result = $this->freeGoogleTranslator->translate('Hello', 'fr', 'en');

        $this->assertEquals('', $result);
    }

    #[Test]
    public function translate(): void
    {
        $text = 'Hello, world!';
        $translatedText = 'Bonjour le monde!';
        $targetLanguage = 'fr';

        $this->variableHandlerMock
            ->method('replacePlaceholders')
            ->willReturn($text);

        $this->variableHandlerMock
            ->method('restorePlaceholders')
            ->willReturn($translatedText);

        $this->translateClientMock
            ->method('setSource')
            ->with('en')
            ->willReturn($this->translateClientMock);

        $this->translateClientMock
            ->method('setTarget')
            ->with($targetLanguage)
            ->willReturn($this->translateClientMock);

        $this->translateClientMock
            ->method('translate')
            ->with($text)
            ->willReturn($translatedText);

        $result = $this->freeGoogleTranslator->translate($text, $targetLanguage);

        $this->assertSame($translatedText, $result);
    }

    #[Test]
    public function translateBatch(): void
    {
        $texts = ['Hello, world!', 'Good morning'];
        $translatedTexts = ['Bonjour le monde!', 'Bonjour'];
        $targetLanguage = 'fr';
        $sourceLanguage = 'en';

        $this->variableHandlerMock
            ->expects($this->exactly(count($texts)))
            ->method('replacePlaceholders')
            ->willReturnArgument(0);

        $this->variableHandlerMock
            ->expects($this->exactly(count($texts)))
            ->method('restorePlaceholders')
            ->willReturnArgument(0);

        $this->translateClientMock
            ->expects($this->exactly(count($texts)))
            ->method('setSource')
            ->with($sourceLanguage)
            ->willReturnSelf();

        $this->translateClientMock
            ->expects($this->exactly(count($texts)))
            ->method('setTarget')
            ->with($targetLanguage)
            ->willReturnSelf();

        $this->translateClientMock->expects($this->exactly(count($texts)))
            ->method('translate')
            ->willReturnOnConsecutiveCalls(...$translatedTexts);

        $result = $this->freeGoogleTranslator->translateBatch($texts, $targetLanguage, $sourceLanguage);

        $this->assertSame(
            [
            'Hello, world!' => 'Bonjour le monde!',
            'Good morning' => 'Bonjour'],
            $result
        );
    }

    #[Test]
    public function testGoogleTranslatorBinding(): void
    {
        $this->assertInstanceOf(GoogleTranslator::class, app(GoogleTranslator::class));
    }

    #[Test]
    public function testGoogleTranslatorHasValidCredentials(): void
    {
        $this->assertTrue($this->freeGoogleTranslator->isConfigured());
    }
}
