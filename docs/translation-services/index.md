---
layout: default
title: Translation Services
nav_order: 3
---
# Translation Services

## Setup
To use `--translate-missing` option, you need to set up a translation service.
This can be done by setting environment variables.

## Default Service
The `default` configuration option specifies the class to be used for automatic translation. This service will be used for translating strings.

You can create your own translation service by implementing the `Bottelet\TranslationChecker\Translator\TranslatorContract` and overwriting the `default` configuration option.

