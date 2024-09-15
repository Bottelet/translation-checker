<?php

namespace Bottelet\TranslationChecker;

use Bottelet\TranslationChecker\Commands\SortTranslation;
use Bottelet\TranslationChecker\Sort\AlphabeticSort;
use Bottelet\TranslationChecker\Sort\SorterContract;
use Bottelet\TranslationChecker\Translator\GoogleTranslator;
use Bottelet\TranslationChecker\Translator\OpenAiTranslator;
use Bottelet\TranslationChecker\Translator\TranslatorContract;
use Bottelet\TranslationChecker\Translator\VariableHandlers\VariableRegexHandler;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\ServiceProvider;
use OpenAI;

class TranslationCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translator.php',
            'translator'
        );

        $this->app->bind(TranslationManager::class, function ($app) {
            return new TranslationManager(
                $app->make(SorterContract::class),
                $app->make(TranslatorContract::class)
            );
        });

        $this->app->bind(GoogleTranslator::class, function ($app) {
            return new GoogleTranslator(
                $app->make(VariableRegexHandler::class),
                new TranslateClient(['keyFile' =>  $app->config['translator.translators.google']])
            );
        });

        $this->app->bind(OpenAiTranslator::class, function ($app) {
            $factory = OpenAI::factory();
            if ($app->config['translator.translators.openai.api_key']) {
                $factory->withApiKey($app->config['translator.translators.openai.api_key']);
            }
            if ($app->config['translator.translators.openai.organization_id']) {
                $factory->withOrganization($app->config['translator.translators.openai.organization_id']);
            }
            return new OpenAiTranslator(
                $factory->withHttpHeader('OpenAI-Beta', 'assistants=v2')->make()
            );
        });

    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/translator.php' => config_path('translator.php'),
        ]);

        $this->app->bind(TranslatorContract::class, function ($app) {
            $defaultService = $app->config['translator.default'];
            $serviceConfig = $app->config["translator.translators.{$defaultService}"];

            if (!isset($serviceConfig['driver'])) {
                throw new \InvalidArgumentException("Driver not specified for the '{$defaultService}' translation service.");
            }
            $driverClass = $serviceConfig['driver'];

            if (!class_exists($driverClass)) {
                throw new \InvalidArgumentException("Driver class '{$driverClass}' does not exist.");
            }

            return $app->make($driverClass);
        });
        $this->app->bind(SorterContract::class, fn ($app) => $app->make(AlphabeticSort::class));



        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CheckTranslation::class,
                Commands\FindMissingTranslation::class,
                Commands\SortTranslation::class,
                Commands\SyncTranslation::class,
                Commands\CleanTranslation::class,
            ]);
        }
    }
}
