<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\Translator\TranslatorContract;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

class TranslationCheckerServiceProviderTest extends TestCase
{
    #[Test]
    public function exceptionThrownWhenDriverTranslatorNotSet()
    {
        $this->app['config']->set('translator.default', 'unknown');

        $this->expectException(InvalidArgumentException::class);
        $this->app->make(TranslatorContract::class);
    }

    #[Test]
    public function exceptionThrownWhenDriverClassDoesNotExist()
    {
        $this->app['config']->set('translator.default', 'testt');
        $this->app['config']->set('translator.translators.testt', [
            'driver' => 'NonExistentDriver',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->app->make(TranslatorContract::class);
    }
}
