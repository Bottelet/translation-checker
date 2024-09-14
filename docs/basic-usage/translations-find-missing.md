---
title: Translations find missing
parent: Basic Usage
nav_order: 3
---
# translations:find-missing

The `translations:find-missing` command is used to find missing translations and add the keys to the given source language file with empty values.

## Usage

php artisan translations:find-missing [options]

### Options

- `--source`: The source language for the translations to find (default: 'en')
- `--print`: Print the missing translations to the console, instead of writing to file

## Description

This command scans your project for translation keys and compares them with the keys in your source language file. It then adds any missing keys to the source file with empty values, ensuring that all used keys are present in the translation file.

## Examples

1. Find missing translations for the default source language (English):
```php
php artisan translations:find-missing
```
2. Find missing translations for French and print the results without updating the file:
```php
php artisan translations:find-missing --source=fr --print
```
3. Find missing translations for German:
```php
php artisan translations:find-missing --source=de
```
The command will either update the source language file with the missing keys (with empty values) or print the missing translations to the console, depending on the options used.