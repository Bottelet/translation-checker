<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Commands\Console\CheckTranslation;
use Bottelet\TranslationChecker\Translator\TranslatorContract;
use Illuminate\Support\ServiceProvider;

class TranslationCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/translator.php' => config_path('translator.php'),
        ]);

        $this->app->bind(TranslatorContract::class, fn ($app) => $app->make($app->config['translator.default_translation_service']));

        $this->commands([
            CheckTranslation::class,
        ]);
    }
}
