---
layout: default
title: Dynamic values
nav_enabled: true
nav_order: 4
---
# Dynamic Values in Laravel Translation
When it comes to handling translations in Laravel applications, we recognize that each developer has their own unique coding style and preferences.

To accommodate different coding styles and project requirements, there are several options for handling dynamic values in translations. Each method has its own strengths, and the choice often depends on personal preference.

## The Challenge with Dynamic Values in Translation
Before diving into the methods for handling dynamic values, it's crucial to understand why they present a unique challenge in translation systems, particularly when interacting with databases.
#### The Core Issue
The primary difficulty with dynamic values lies in determining when a string should be translated and when it should remain in its original form. This becomes especially problematic when dealing with data that is both stored in and retrieved from a database.
Consider the following scenario:

```php
$message = "User updated their account"; // We dont wish to store the translation so we can't use __(), to to find the string
ActivityLog::create(['name' => 'John', 'message' => $message]);

$user = ActivityLog::first();
echo __($user->message); // We want this translated
```
Let's look into how we can solve this problem.
## Methods for Handling Dynamic Values

### 1. Using @translate Comments
This method uses special comments to indicate which parts of a string should be translated. It's particularly useful for developers who prefer to keep their translation logic close to the string definitions.

```php
/** @translate */
$message = "User updated their account"; 
ActivityLog::create(['name' => 'John', 'message' => $message]);

$user = ActivityLog::first();
echo __($user->message); 
```
The `@translate` comment tells the translator to look for the string below and will find and add "User updated their account" to the translation file.

And this works great for a simple string, but what if we want to translate a string that contains dynamic values?
```php
/** @translate<User updated their account, User deleted their account, User created their account> */
$message = UserAction::getLogText($user);
ActivityLog::create(['name' => 'John', 'message' => $message]);

$user = ActivityLog::first();
echo __($user->message);
```
In the above example we might not be 100% sure what the dynamic value is at runtime, but know the  different options that could be used.
So it will add all the options to the translation file.

### 2. Using a custom helper
For developers who favor explicit marking of translatable strings, a custom helper function can be created.

This function needs to be created by yourself. The function name can be anything you want, and can be overwritten in the configuration file `translator.noop_translation`

```php
function __t(string $string): string
{
    return $string;
}
```
```php
class UserAction
{
    public static function getLogText(User $user): string
    {
        match ($user->action) {
            UserAction::CREATED => __t('User created their account'),
            UserAction::UPDATED => __t('User updated their account'),
            UserAction::DELETED => __t('User deleted their account'),
        };
    }
}
```
The translator will find all __t() functions and just return the original string. but will be able to find the string and add it to the translation file.

### 3. Using Configuration in persistent_keys
In the configuration file `translator.persistent_keys` you can add strings that should always be translated, and never be deleted.

```php
'persistent_keys' => [
    'User created their account',
    'User updated their account',
    'User deleted their account',
],
```
These translations will always be added to the translation file, even if they are not found in the code.

### Enums values
Enums present a special case in translations, as they are often quite easy to translate.
```php
   $translations = [
        __(StatusEnum::PENDING_TRIAL->label()), // Pending trial
        __(StatusEnum::DONE->name), // DONE
        __(StatusEnum::FAILED->value), // failed
        __(StatusEnum::FAILED->customMethod()), // Will not be found
    ];
```
The translator will understand the outcome of the enum and add it to the translation file.

It understands the ->label() function and will add the label to the translation file, following the convention of [MrPunyapal](https://gist.github.com/MrPunyapal/7744c5ab8e4e85c740899f37c3a68b03)
