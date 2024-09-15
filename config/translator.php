<?php

return [
    'default_translation_service' => \Bottelet\TranslationChecker\Translator\OpenAiTranslator::class,
    'translators' => [
        'google' => [
            'type' => env('GOOGLE_TRANSLATE_TYPE', 'service_account'),
            'project_id' => env('GOOGLE_TRANSLATE_PROJECT_ID'),
            'private_key' => env('GOOGLE_TRANSLATE_PRIVATE_KEY'),
            'client_email' => env('GOOGLE_TRANSLATE_CLIENT_EMAIL'),
            'client_x509_cert_url' => env('GOOGLE_TRANSLATE_CLIENT_CERT_URL'),
        ],
        'openai' => [
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
];
