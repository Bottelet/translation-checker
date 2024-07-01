<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\TranslatorContract;
use Illuminate\Support\ServiceProvider;

class TranslationCheckerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TranslatorContract::class, GoogleTranslator::class);
    }

    public function boot(): void
    {

    }
}
