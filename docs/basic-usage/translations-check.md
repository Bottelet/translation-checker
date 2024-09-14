---
title: Translations check
layout: default
parent: Basic Usage
nav_order: 1
---

# translations:check

The `translations:check` command is used to find missing translations in your project.

## Usage

php artisan translations:check {target} [options]

### Arguments

- `target`: The target language for the translations

### Options

- `--source`: The source language used for the translation provider (default: 'en')
- `--translate-missing`: Translate missing translations using the translation service
- `--sort`: Sort JSON translation files

## Description
This command checks for missing translations in the config source_paths files, and finds missing translations and add them accordingly. It can optionally translate missing entries and sort the JSON files.

When providing the `--translate-missing` option, the command will need a source language to translate the missing entries. This will generally be the primary language used in your application, defaulting to `en`.

## Examples

1. Check translations for French:
```php
php artisan translations:check fr
```
2. Check translations for German, translating missing entries and sorting the file:
```php
php artisan translations:check de --translate-missing --sort
```
3. Check translations for Spanish, using Italian as the source language:
```php
php artisan translations:check es --source=it
```
The command will display the results, showing whether any missing translations were found and updated.