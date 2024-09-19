<?php

namespace Bottelet\TranslationChecker\Tests\Translator;

use Bottelet\TranslationChecker\Translator\DeeplTranslator;
use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use DeepL\TextResult;
use DeepL\Translator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class DeeplTranslatorTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    /** @var VariableRegexHandler|MockObject */
    private $variableHandlerMock;

    /** @var Translator|MockObject */
    private $translateClientMock;

    /** @var DeeplTranslator */
    private $deeplTranslator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('translator.translators.deepl', [
            'api_key' => 'test-key',
        ]);

        $this->translateClientMock = $this->createMock(Translator::class);
        $this->variableHandlerMock = $this->createMock(VariableRegexHandler::class);
        $this->deeplTranslator = new DeeplTranslator($this->variableHandlerMock, $this->translateClientMock);
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

        $this->translateClientMock->method('translateText')
            ->willReturn(new TextResult($translatedText, 'en', 0));

        $result = $this->deeplTranslator->translate($text, $targetLanguage);

        $this->assertSame($translatedText, $result);
    }

    #[Test]
    public function translateBatch(): void
    {
        $texts = ['Hello, world!', 'Good morning'];
        $translatedTexts = ['Bonjour le monde!', 'Bonjour'];
        $targetLanguage = 'fr';

        $translations = array_map(fn ($text) => new TextResult($text, 'en', 0), $translatedTexts);

        $this->variableHandlerMock->expects($this->exactly(count($texts)))
            ->method('replacePlaceholders')
            ->willReturnOnConsecutiveCalls(...$texts);

        $this->variableHandlerMock->expects($this->exactly(count($texts)))
            ->method('restorePlaceholders')
            ->willReturnOnConsecutiveCalls(...$translatedTexts);

        $this->translateClientMock->expects($this->once())
            ->method('translateText')
            ->willReturn($translations);

        $result = $this->deeplTranslator->translateBatch($texts, $targetLanguage);

        $this->assertSame(['Hello, world!' => 'Bonjour le monde!', 'Good morning' => 'Bonjour'], $result);
    }

    #[Test]
    public function testDeeplTranslatorBinding(): void
    {
        $this->assertInstanceOf(DeeplTranslator::class, app(DeeplTranslator::class));
    }

    #[Test]
    public function testDeeplTranslatorHasValidCredentials(): void
    {
        $this->assertTrue($this->deeplTranslator->isConfigured());
    }

    #[Test]
    public function testDeeplTranslatorHasInvalidCredentials(): void
    {
        $this->app['config']->set('translator.translators.deepl', [
            'api_key' => null,
        ]);
        $this->assertFalse($this->deeplTranslator->isConfigured());
    }
}
