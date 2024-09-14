---
title: Translations sorting
parent: Basic Usage
nav_order: 4
---

# translations:sort

The `translations:sort` command is used to sort translation files by keys using the sorter specified in the configuration.

## Usage

php artisan translations:sort [options]

### Options

- `--source`: The source language for the translations to sort (default: 'en')
- `--all`: Sort all files found in the configured language folder

## Description

This command sorts the translation keys in the specified language file(s) using the sorting method defined in your configuration. It helps maintain a consistent order across your translation files, making them easier to manage and compare.

## Examples

1. Sort translations for the default source language (English):
```php
php artisan translations:sort
```
2. Sort translations for French:
```php
php artisan translations:sort --source=fr
```
3. Sort all translation files in the configured language folder:
```php
php artisan translations:sort --all
```
The command will sort the specified translation file(s) and update them with the sorted keys.