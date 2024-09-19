---
layout: default
title: Translation service
nav_enabled: true
nav_order: 3
---

## Translation Services
### Setup
To use `--translate-missing` option, you need to set up a translation service.
This can be done by setting environment variables.

For OpenAI, you need to set the following environment variables:

```bash
OPENAI_API_KEY=your_api_key
OPENAI_API_BASE=your_api_base
```
For more information, see [OpenAI Setup](https://platform.openai.com/docs/guides/production-best-practices/setting-up-your-organization)

For Google Translate, you need to set the following environment variables
```bash
GOOGLE_TRANSLATE_TYPE=service_account
GOOGLE_TRANSLATE_PROJECT_ID=your_project_id
GOOGLE_TRANSLATE_PRIVATE_KEY=your_private_key
GOOGLE_TRANSLATE_CLIENT_EMAIL=your_client_email
GOOGLE_TRANSLATE_CLIENT_CERT_URL=your_client_cert_url
```
See more here: [Google Translate Setup](https://cloud.google.com/translate/docs/setup)

For DeepL, you need to set the following environment variable
```bash
DEEPL_API_KEY=your_api_key
```
See more here: [DeepL API Authentication](https://developers.deepl.com/docs/getting-started/auth#authentication)

### Default Service
The `default` configuration option specifies the class to be used for automatic translation. This service will be used for translating strings.

There are currently three built-in translation services:
1. **GoogleTranslateService** - Translates strings using Google Translate
2. **OpenAIService** - Translates strings using OpenAI's API
3. **DeepLService** - Translates strings using DeepL's API


You can create your own translation service by implementing the `Bottelet\TranslationChecker\Translator\TranslatorContract` and overwriting the `default` configuration option.
