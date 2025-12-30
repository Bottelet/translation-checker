# Translation Checker
Easily **translate missing translations with AI**, **find missing translations** you forgot to add to your language files, and **maintain translations** in your project. It provides a set of commands to help with language files, ensuring consistency and completeness across your translations.
It works with Laravel and supports various frontend frameworks like Vue.js, React, and Svelte.

> This package is only intended for Laravel's recommended approach to handle translation files. See Laravel [Docs](https://laravel.com/docs/12.x/localization#:~:text=This%20approach%20is%20recommended%20for%20applications%20that%20have%20a%20large%20number%20of%20translatable%20strings%3A<)

## How it works
1. Scan Source Files: The system looks through your code for strings that need translation.
2. Check Language Files: It then checks if these strings exist in your language files.
3. Use AI to translate missing keys: It adds the missing key with empty values if there is no translation service used.

### Example:

`lang.json`
```json
{
  "test.value": "Value"
}
```

`lang.php`
```php
return [
  'test.value' => 'Value',
];
```

TestClass.php

```php
class TestClass
{
    public function getValue()
    {
        return __('translate this string');
    }
}
```
```bash 
php artisan translations:check en
```
`lang.json`
```json
{
  "test.value": "Value",
  "translate this string": null
}
```
`lang.php`
```php
return [
  'test.value' => 'Value',
  'translate this string' => null,
];
```
The reason we default to null when no translation service are used is because it defaults to the key used in the function call.

## Quick Start

Install the package via composer:
```bash
  composer require bottelet/translation-checker --dev
```

## Usage

Translation Checker provides several commands to manage your translations. The most versatile is the `check` command:
```bash
  php artisan translations:check en 
```

You can also easily find all sync translations between all files:
```bash
  php artisan translations:sync en
```
This will take all translations from the source files and sync them to all your other language files.

For detailed information on all available commands and their usage, refer to the [documentation](https://bottelet.github.io/translation-checker/).

## Testing

Run the tests with:

```bash
  ./vendor/bin/phpunit
```

Run PHPStan to check for code quality:
```bash
  ./vendor/bin/phpstan
```

## Documentation

For full documentation, visit our [GitHub documentation page](https://bottelet.github.io/translation-checker/).

If you encounter any issues or have suggestions, please create an issue on GitHub.

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.