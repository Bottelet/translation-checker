<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\Exception\TranslationServiceException;
use Bottelet\TranslationChecker\Sort\AlphabeticSort;
use Bottelet\TranslationChecker\TranslationManager;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\TranslatorContract;
use FilesystemIterator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TranslationCheckerServiceProviderTest extends \Bottelet\TranslationChecker\Tests\TestCase
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
