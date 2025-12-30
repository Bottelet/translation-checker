<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\ExtractorFactory;
use Bottelet\TranslationChecker\Extractor\RegexExtractor;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class RegexExtractorTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $testPhpContent = <<<'TEXT'
         __('simple_string');
         __('String with "double quotes"');
         __('custom_translator', 'Another simple string');
         __('with :variable test', ['variable' => 'test']);
         __('with :variable test and line breaks', [
             'variable' => 'test'
         ]);
        $t('dollar t');
        t("t translation")
        t("t translation with parameters", { parameter: someValue, another: 'value' })
        t("t translation with parameters and line breaks", { 
            parameter: 'someValue',
            another: 'value' 
        })
        tc("t translation with pluralization", 2)
        $_('underscore translator')
        { $t("dollar t inside curly bracket") }
        {{ $_('underscore translator inside curly bracket') }}
        {{ t("t translation inside curly brackets") }}
        TEXT;

        file_put_contents($this->tempDir . '/test.php', $testPhpContent);
    }

    #[Test]
    public function it_correctly_extracts_translation_strings_from_php_file(): void
    {
        $file = new SplFileInfo($this->tempDir . '/test.php');
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertCount(14, $translationKeys);
        $this->assertContains('with :variable test', $translationKeys);
        $this->assertContains('dollar t inside curly bracket', $translationKeys);
        $this->assertContains('simple_string', $translationKeys);
        $this->assertContains('underscore translator inside curly bracket', $translationKeys);
    }

    #[Test]
    public function it_handles_files_without_translation_strings(): void
    {
        $file = $this->createTempFile('empty.php', '<?php // No translations here');
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertEmpty($translationKeys);
    }

    #[Test]
    public function it_handles_vue_js_files_with_translations(): void
    {
        $extractor = new RegexExtractor;
        $translationKeys = $extractor->extractFromFile($this->vueFile);

        $this->assertContains('welcome_message', $translationKeys);
        $this->assertNotContains('Clicked', $translationKeys);
    }

    #[Test]
    public function should_not_find_emit_request_etc(): void
    {
        $file = $this->createTempFile('file.js', '<template>this.$emit(\'emit\'); this.request(\'request\'); $t(\'findable\') const target = target("target") </template>');
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);
        $this->assertContains('findable', $translationKeys);
        $this->assertNotContains('emit', $translationKeys);
        $this->assertNotContains('request', $translationKeys);
        $this->assertNotContains('target', $translationKeys);
    }

    #[Test]
    public function it_accepts_a_new_added_pattern(): void
    {
        $testPhpContent = <<<'TEXT'
         mytranslatefunction('simple_string');
         mytranslatefunction('String with "double quotes"');
         mytranslatefunction('with :variable test', ['variable' => 'test']);
        TEXT;

        file_put_contents($this->tempDir . '/test.php', $testPhpContent);

        $file = new SplFileInfo($this->tempDir . '/test.php');
        $extractor = new RegexExtractor;

        $extractor->addPattern('/(mytranslatefunction\()([\'"])(.*?)\2/', 3, 'mytranslatefunction');

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertCount(3, $translationKeys);
        $this->assertContains('simple_string', $translationKeys);
        $this->assertContains('String with "double quotes"', $translationKeys);
        $this->assertContains('with :variable test', $translationKeys);
    }

    #[Test]
    public function it_accepts_a_new_added_pattern_that_matches_specific_cases(): void
    {
        $testPhpContent = <<<'TEXT'
         mytranslatefunction('simple_string');
         mytranslatefunction('String with "double quotes"');
         mytranslatefunction('with :variable test', ['variable' => 'test']);
        TEXT;

        file_put_contents($this->tempDir . '/test.php', $testPhpContent);

        $file = new SplFileInfo($this->tempDir . '/test.php');
        $extractor = new RegexExtractor;

        // Pattern that does not match translations with variables
        $extractor->addPattern('/mytranslatefunction\((["\'])(.*?)\1\)/', 2, 'mytranslatefunction');

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertCount(2, $translationKeys);
        $this->assertContains('simple_string', $translationKeys);
        $this->assertContains('String with "double quotes"', $translationKeys);

        // Assert that the pattern does not match translations with variables
        $this->assertNotContains('with :variable test', $translationKeys);
    }

    #[Test]
    public function it_resolves_extractor_bound_to_the_app(): void
    {
        app()->bind(RegexExtractor::class, function () {
            return (new RegexExtractor)->addPattern(
                regex: '/mytranslatefunction\((["\'])(.*?)\1\)/',
                matchIndex: 2,
                group: 'mytranslatefunction'
            );
        });

        $testPhpContent = <<<'TEXT'
         mytranslatefunction('simple_string');
         mytranslatefunction('String with "double quotes"');
        TEXT;

        // Create a '.something' file to test if the extractor is resolved correctly
        $fileName = $this->tempDir . '/test.js';
        file_put_contents($fileName, $testPhpContent);
        $file = new SplFileInfo($fileName);

        $extractor = ExtractorFactory::createExtractorForFile($file);
        $translationKeys = $extractor->extractFromFile($file);

        $this->assertCount(2, $translationKeys);
        $this->assertContains('simple_string', $translationKeys);
        $this->assertContains('String with "double quotes"', $translationKeys);
    }

    #[Test]
    public function it_extracts_multiline_translations_from_vue_files(): void
    {
        $vueContent = <<<'TEXT'
<template>
    <div>
        <p>{{ $t('simple_key') }}</p>
        <button>
            {{ $t(
                'Press Enter or comma to add. These will be used as default for all contacts under this company.'
            ) }}
        </button>
        <span>{{ t(
            'Another multi-line translation'
        ) }}</span>
    </div>
</template>
TEXT;

        $file = $this->createTempFile('multiline.vue', $vueContent);
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertContains('simple_key', $translationKeys);
        $this->assertContains('Press Enter or comma to add. These will be used as default for all contacts under this company.', $translationKeys);
        $this->assertContains('Another multi-line translation', $translationKeys);
    }

    #[Test]
    public function it_extracts_multiline_translations_from_blade_files(): void
    {
        $bladeContent = <<<'TEXT'
<div>
    {{ $t(
        'Long translation text that spans multiple lines for better readability'
    ) }}
</div>
TEXT;

        $file = $this->createTempFile('multiline.blade.php', $bladeContent);
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertContains('Long translation text that spans multiple lines for better readability', $translationKeys);
    }

    #[Test]
    public function it_extracts_multiline_translations_from_react_files(): void
    {
        $reactContent = <<<'TEXT'
export default function Component() {
    return (
        <div>
            {t(
                'React translation with multiple lines'
            )}
        </div>
    );
}
TEXT;

        $file = $this->createTempFile('multiline.jsx', $reactContent);
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertContains('React translation with multiple lines', $translationKeys);
    }

    #[Test]
    public function it_extracts_multiline_translations_from_svelte_files(): void
    {
        $svelteContent = <<<'TEXT'
<script>
    import { t } from '$lib/i18n';
</script>

<div>
    {$t(
        'Svelte translation with multiple lines'
    )}
</div>
TEXT;

        $file = $this->createTempFile('multiline.svelte', $svelteContent);
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertContains('Svelte translation with multiple lines', $translationKeys);
    }
}
