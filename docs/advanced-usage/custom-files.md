---
title: Custom files
layout: default
parent: Advanced Usage
nav_order: 1
---

# Custom files

## Extending the RegexExtractor for Custom File Types

By default, this package scans `.blade.php` and `.php` files for translation function calls. However, if you have implemented translation functions in other file types such as JavaScript (`.js`) or Vue (`.vue`)
the package will automatically fallback to the `RegexExtractor`.

This allows you to extract translation strings from any file type using regular expressions.

### Customizing the RegexExtractor

You can extend the `RegexExtractor` to match your custom translation functions by adding custom regex patterns.

This is done by binding a new instance of `RegexExtractor` in your `AppServiceProvider` and defining your patterns using the `addPattern` method.

#### Example 1: Add Custom Function

Suppose you have a custom translation function called `myTranslateFunction()` in your JavaScript files.

You can configure the `RegexExtractor` like this:

```php
use App\Providers\AppServiceProvider;
use Vendor\Package\Extractors\RegexExtractor;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        app()->bind(RegexExtractor::class, function () {
            return (new RegexExtractor)
            ->addPattern(
                regex: '/myTranslateFunction\((["\'])(.*?)\1\)/',
                matchIndex: 2,
                group: 'myTranslateFunction'
            );
        });
    }
}
```

This pattern will match instances like:

```javascript
myTranslateFunction('Hello, world!')
```

Parameters Explained
- regex: The regular expression pattern that matches your translation function calls.
- matchIndex: The index of the capturing group in your regex that contains the string to extract.
- group: A label used to group extracted strings from similar functions.
