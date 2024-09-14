# translations:sync

The `translations:sync` command is used to synchronize translations between language files.

## Usage

php artisan translations:sync [options]

### Options

- `--source`: The source language file to sync from (default: 'en')
- `--target`: The target language file to sync to (optional)

## Description

This command synchronizes translations between language files. It can either sync from a source language file to a specific target language file, or sync from the source to all other language files in the configured language folder.

## Examples

1. Sync translations from the default source language (English) to all other language files:
```php
php artisan translations:sync
```

2. Sync translations from French to German:
```php
php artisan translations:sync --source=fr --target=de
```

3. Sync translations from Spanish to all other language files:
```php
php artisan translations:sync --source=es
```


The command will update the target language file(s) with any missing keys from the source file, ensuring consistency across your translations.