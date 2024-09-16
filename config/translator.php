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

    /**
     * Defines the function used to mark strings for translation without actually translating them.
     *
     * When this function (e.g., '__t') is used, it returns the original string unchanged. This is useful
     * for cases where the string should be saved in the database or processed without translation, but still
     * needs to be flagged for translation in the future.
     *
     * Set this to the name of the function that will act as a no-op for translation (e.g., '__t').
     */
    'noop_translation' => '__t',

    /**
     * A list of specific strings or keys that should always be included in translation files.
     *
     * These strings will remain in the translation system even if they are not actively found
     * during clean processes. This is useful for reserved words, fallback keys, or
     * any content that should be protected from deletion or always be translated.
     *
     * Add the words or keys that need to be permanently available for translators.
     */
    'persistent_keys' => [],
];
