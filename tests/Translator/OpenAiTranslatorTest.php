<?php

namespace Bottelet\TranslationChecker\Tests\Translator;

use Bottelet\TranslationChecker\Translator\OpenAiTranslator;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;
use PHPUnit\Framework\Attributes\Test;

class OpenAiTranslatorTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    private ClientFake $translateClientMock;
    private OpenAiTranslator $openAiTranslator;

    #[Test]
    public function translate(): void
    {
        $this->translateClientMock = new ClientFake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'role'    => 'assistant',
                            'content' => "Bonjour :name",
                        ],
                    ],
                ],
            ])
        ]);
        $this->openAiTranslator = new OpenAiTranslator($this->translateClientMock);

        $res = $this->openAiTranslator->translate('Hello :name', 'fr', 'en');
        $this->assertEquals('Bonjour :name', $res);
    }


    #[Test]
    public function batchTranslate(): void
    {
        $this->translateClientMock = new ClientFake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'role'    => 'assistant',
                            'content' => '{"Hello :name": "Hola :name", "Welcome to our :service!": "Bienvenue dans notre :service !", "Please contact :support for assistance.": "Veuillez contacter :support pour obtenir de l\'aide."}',
                        ],
                    ],
                ],
            ])
        ]);
        $this->openAiTranslator = new OpenAiTranslator($this->translateClientMock);

        $res = $this->openAiTranslator->translateBatch(['Hello :name', 'Welcome to our :service!', 'Please contact :support for assistance.'], 'es', 'en');
        $this->assertEquals(['Hello :name' => 'Hola :name', 'Welcome to our :service!' => 'Bienvenue dans notre :service !', 'Please contact :support for assistance.' => 'Veuillez contacter :support pour obtenir de l\'aide.'], $res);
    }
}
