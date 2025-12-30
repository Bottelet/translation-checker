<?php

return [

    /*
      |--------------------------------------------------------------------------
      | PHP Version
      |--------------------------------------------------------------------------
      |
      | The PHP version makes the parser aware of version-specific syntax
      | e.g., in PHP 8.4 introduced multiple access type modifiers to properties
      |
      | Supported: "8.4", "8.3", "8.2"
      |
      | Default: 8.2
      |
      */
    'php_version' => env('TRANSLATOR_PHP_VERSION', '8.5'),
    /*
      |--------------------------------------------------------------------------
      | Default Translation Service
      |--------------------------------------------------------------------------
      |
      | This option controls the default translation service that gets used when
      | using this translation library. This service is used when another is
      | not explicitly specified when executing a given translation function.
      |
      | Supported: "google", "openai", "deepl"
      |
      */
    'default' => env('DEFAULT_TRANSLATOR_SERVICE', 'free_google'),
    'translators' => [
        'free_google' => [
            'driver' => Bottelet\TranslationChecker\Translator\FreeGoogleTranslator::class,
        ],
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

            /**
             * Custom added prompt to enhance translation quality.
             *
             * Example:
             * 'prompt_extension' => 'This application translates medical terms consistently throughout.'
             */
            'prompt_extension' => ''
        ],
        'deepl' => [
            'driver' => Bottelet\TranslationChecker\Translator\DeeplTranslator::class,
            'api_key' => env('DEEPL_API_KEY'),
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
