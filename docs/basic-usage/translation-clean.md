---
title: Translations clean
parent: Basic Usage
nav_order: 2
---

# translations:clean

The `translations:clean` command is used to clean translations by removing unused keys from the source language file.

## Usage

php artisan translations:clean [options]

### Options

- `--source`: The source language for the translations to clean (default: 'en')
- `--print`: Print the cleaned translations to the console, instead of updating the file

## Description

This command removes unused translation keys from the specified source language file. It compares the keys in the source file with those actually used in your project and removes any that are not in use.

## Examples

1. Clean translations for the default source language (English):
```php
php artisan translations:clean
```
2. Clean translations for French and print the results without updating the file:
```php
php artisan translations:clean --source=fr --print
```
3. Clean translations for German:
```php
php artisan translations:clean --source=de
```
The command will either update the source language file, removing unused keys, or print the cleaned translations to the console, depending on the options used.