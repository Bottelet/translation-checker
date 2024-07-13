<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Translator\TranslatorContract;
use Illuminate\Support\ServiceProvider;

class TranslationCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TranslationManager::class, function ($app) {
            return new TranslationManager(
                $app->make(FileManagement::class),
                $app->make(TranslationFinder::class),
                $app->make(JsonTranslationFileManager::class),
                $app->make(TranslatorContract::class)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/translator.php' => config_path('translator.php'),
        ]);

        $this->app->bind(TranslatorContract::class, fn ($app) => $app->make($app->config['translator.default_translation_service']));

        $this->commands([
            Commands\CheckTranslation::class,
        ]);
    }
}
