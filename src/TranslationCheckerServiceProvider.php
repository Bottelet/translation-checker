<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Sort\AlphabeticSort;
use Bottelet\TranslationChecker\Sort\Sorter;
use Bottelet\TranslationChecker\Sort\SorterContract;
use Bottelet\TranslationChecker\Translator\TranslatorContract;
use Illuminate\Support\ServiceProvider;

class TranslationCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TranslationManager::class, function ($app) {
            return new TranslationManager(
                $app->make(SorterContract::class),
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
        $this->app->bind(SorterContract::class, fn ($app) => $app->make(AlphabeticSort::class));

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CheckTranslation::class,
                Commands\FindMissing::class,
            ]);
        }
    }
}
