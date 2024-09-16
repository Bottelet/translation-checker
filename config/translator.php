<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Default Translation Service
      |--------------------------------------------------------------------------
      |
      | This option controls the default translation service that gets used when
      | using this translation library. This service is used when another is
      | not explicitly specified when executing a given translation function.
      |
      | Supported: "google", "openai"
      |
      */
    'default' => env('DEFAULT_TRANSLATOR_SERVICE', 'openai'),
    'translators' => [
        'google' => [
            'driver' => Bottelet\TranslationChecker\Translator\GoogleTranslator::class,
            'type' => env('GOOGLE_TRANSLATE_TYPE', 'service_account'),
            'project_id' => env('GOOGLE_TRANSLATE_PROJECT_ID'),
            'private_key' => env('GOOGLE_TRANSLATE_PRIVATE_KEY'),
            'client_email' => env('GOOGLE_TRANSLATE_CLIENT_EMAIL'),
            'client_x509_cert_url' => env('GOOGLE_TRANSLATE_CLIENT_CERT_URL'),
        ],
        'openai' => [
            'driver' => Bottelet\TranslationChecker\Translator\OpenAiTranslator::class,
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'api_key' => env('OPENAI_API_KEY'),
            'organization_id' => env('OPENAI_ORGANIZATION'),
        ],
    ],
    'source_paths' => [
        base_path('app/'),
        base_path('resources/'),
    ],
    'language_folder' => base_path('/lang'),
    'noop_translation' => '__t',
];
