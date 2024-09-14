# Translation Checker
Translation Checker is tool designed to help you find translations you forgot to add to your language files, check, and maintain translations in your project. It provides a set of commands to help with language files, ensuring consistency and completeness across your translations.

It works with Laravel and supports various frontend frameworks like Vue.js, React, and Svelte.

## Quick Start

Install the package via composer:
```bash
php artisan composer require bottelet/translation-checker
```

## Usage

Translation Checker provides several commands to manage your translations. The most versatile is the `check` command:
```bash
php artisan translations:check en 
```
For detailed information on all available commands and their usage, refer to the [documentation](https://bottelet.github.io/translation-checker/).

## Testing

Run the tests with:

```bash
./vendor/bin/phpunit
```

## Documentation

For full documentation, visit our [GitHub documentation page](https://bottelet.github.io/translation-checker/).

If you encounter any issues or have suggestions, please create an issue on GitHub.

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.