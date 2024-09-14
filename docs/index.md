---
layout: default
title: Getting Started
nav_enabled: true
nav_order: 1
---

# Translation Checker
Translation Checker is tool designed to help you find translations you forgot to add to your language files, check, and maintain translations in your project. It provides a set of commands to help with language files, ensuring consistency and completeness across your translations.

It works with Laravel and supports various frontend frameworks like Vue.js, React, and Svelte.

and as default looks for translations in the `app/` and `resources/` folders.
## Installation
You can install the package via composer: 

```bash
composer require bottelet/laravel-translation-checker
```

## Configuration
The Translation Checker can be configured using a PHP configuration file. This file allows you to set up translation services, specify source paths, and define the language folder.

The standard config tries to keep up with Laravel's default structure, but you can customize it if you need by publishing the configuration file.

```php
php artisan vendor:publish --provider="Bottelet\TranslationChecker\TranslationCheckerServiceProvider"
```

## Available Commands

1. **translations:check** - Check, manage, and update translations
2. **translations:clean** - Clean translations by removing unused keys
3. **translations:find-missing** - Find and add missing translations
4. **translations:sort** - Sort translation files
5. **translations:sync** - Sync translations between language files

## General Usage

To use Translation Checker, run the desired command in your terminal, followed by any required arguments or options. Each command is designed to help with a specific aspect of translation management.

The `check` command is particularly versatile. It scans the specified `source_paths` to find missing translations, can add them to your language files, and even translate them using the configured translation service.

For detailed information on each command, please refer to their individual documentation pages in the Basic Usage section:

- [translations:check](basic-usage/translations-check.md)
- [translations:clean](basic-usage/translations-clean.md)
- [translations:find-missing](basic-usage/translations-find-missing.md)
- [translations:sort](basic-usage/translations-sort.md)
- [translations:sync](basic-usage/translations-sync.md)


## Translation Services
### Setup
To use `--translate-missing` option, you need to set up a translation service.
This can be done by setting environment variables.

For OpenAI, you need to set the following environment variables:

```bash
OPENAI_API_KEY=your_api_key
OPENAI_API_BASE=your_api_base
```
For more information, see [OpenAI Setup](https://platform.openai.com/docs/guides/production-best-practices/setting-up-your-organization)

For Google Translate, you need to set the following environment variables
```bash
GOOGLE_TRANSLATE_TYPE=service_account
GOOGLE_TRANSLATE_PROJECT_ID=your_project_id
GOOGLE_TRANSLATE_PRIVATE_KEY=your_private_key
GOOGLE_TRANSLATE_CLIENT_EMAIL=your_client_email
GOOGLE_TRANSLATE_CLIENT_CERT_URL=your_client_cert_url
```
See more here: [Google Translate Setup](https://cloud.google.com/translate/docs/setup)

### Default Service
The `default_translation_service` configuration option specifies the class to be used for automatic translation. This service will be used for translating strings.

There are currently two built-in translation services:
1. **GoogleTranslateService** - Translates strings using Google Translate
2. **OpenAIService** - Translates strings using OpenAI's API


You can create your own translation service by implementing the `Bottelet\TranslationChecker\Translator\TranslatorContract` and overwriting the `default_translation_service` configuration option.
