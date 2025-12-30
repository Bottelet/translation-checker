<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\RegexExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RegexExtractorPatternsTest extends TestCase
{
    #[Test]
    public function doubleUnderscorePattern(): void
    {
        $doubleUnderscorePattern = RegexExtractor::DOUBLE_UNDERSCORE_SYNTAX_PATTERN;
        $contents = "{{ __('welcome') }} lorem ipsum {!! __('user') !! }}";
        preg_match_all($doubleUnderscorePattern, $contents, $matches);

        $this->assertEquals(['welcome', 'user'], $matches[3]);
    }

    #[Test]
    public function doubleUnderscorePatternWithVariables(): void
    {
        $doubleUnderscorePattern = RegexExtractor::DOUBLE_UNDERSCORE_SYNTAX_PATTERN;
        $contents = "some random text __('case with :key', [key => 'key']) more random text __('another_case', 'test')";
        preg_match_all($doubleUnderscorePattern, $contents, $matches);

        $this->assertContains('case with :key', $matches[3]);
        $this->assertContains('another_case', $matches[3]);
        $this->assertNotContains('more random text', $matches[3]);
    }

    #[Test]
    public function tPattern(): void
    {
        $tPattern = RegexExtractor::T_SYNTAX_PATTERN;

        $contents = "<template>{{ \$t('welcome_t') }} not me {{ \$t('Hello t world') }}  {{ t('only t in bracket') }} ({{ t('within parens with params', { param: 'value' })}}) t('only t withoutbrackets')</template>";
        preg_match_all($tPattern, $contents, $matches);

        $this->assertContains('Hello t world', $matches[2]);
        $this->assertCount(5, $matches[2]);
    }

    #[Test]
    public function tPatternAdditional(): void
    {
        $tPattern = RegexExtractor::T_SYNTAX_PATTERN;

        $contents = "let text = t('vue_case'); let anotherText = \$t('dollar_case');";
        preg_match_all($tPattern, $contents, $matches);

        $this->assertEquals(['vue_case', 'dollar_case'], $matches[2]);
    }

    #[Test]
    public function dollarUnderscorePattern(): void
    {
        $dollarUnderscorePattern = RegexExtractor::DOLLAR_UNDERSCORE_PATTERN;
        $contents = "Some text {\$_('welcome_underscore')} and more text \$_('another_underscore')";
        preg_match_all($dollarUnderscorePattern, $contents, $matches);

        $this->assertEquals(['welcome_underscore', 'another_underscore'], $matches[1]);
    }

    #[Test]
    public function dollarUnderscorePatternAdditional(): void
    {
        $dollarUnderscorePattern = RegexExtractor::DOLLAR_UNDERSCORE_PATTERN;
        $contents = "Initial text \$_('first_underscore_case'); Ending text {\$_('second_underscore_case')};";
        preg_match_all($dollarUnderscorePattern, $contents, $matches);

        $this->assertEquals(['first_underscore_case', 'second_underscore_case'], $matches[1]);
    }

    #[Test]
    public function tPatternHandlesMultilineStrings(): void
    {
        $tPattern = RegexExtractor::T_SYNTAX_PATTERN;

        $contents = <<<'TEXT'
$t(
    'Press Enter or comma to add. These will be used as default for all contacts under this company.'
)
TEXT;
        preg_match_all($tPattern, $contents, $matches);

        $this->assertCount(1, $matches[2]);
        $this->assertEquals('Press Enter or comma to add. These will be used as default for all contacts under this company.', $matches[2][0]);
    }

    #[Test]
    public function tPatternHandlesMultilineStringsInVueBladeReactSvelte(): void
    {
        $tPattern = RegexExtractor::T_SYNTAX_PATTERN;

        $vueContent = <<<'TEXT'
<template>
    <div>
        {{ $t(
            'This is a multi-line translation key in Vue'
        ) }}
    </div>
</template>
TEXT;

        $bladeContent = <<<'TEXT'
<div>
    {{ $t(
        'This is a multi-line translation key in Blade'
    ) }}
</div>
TEXT;

        $reactContent = <<<'TEXT'
<div>
    {t(
        'This is a multi-line translation key in React'
    )}
</div>
TEXT;

        $svelteContent = <<<'TEXT'
<div>
    {$t(
        'This is a multi-line translation key in Svelte'
    )}
</div>
TEXT;

        preg_match_all($tPattern, $vueContent, $vueMatches);
        preg_match_all($tPattern, $bladeContent, $bladeMatches);
        preg_match_all($tPattern, $reactContent, $reactMatches);
        preg_match_all($tPattern, $svelteContent, $svelteMatches);

        $this->assertContains('This is a multi-line translation key in Vue', $vueMatches[2]);
        $this->assertContains('This is a multi-line translation key in Blade', $bladeMatches[2]);
        $this->assertContains('This is a multi-line translation key in React', $reactMatches[2]);
        $this->assertContains('This is a multi-line translation key in Svelte', $svelteMatches[2]);
    }

    #[Test]
    public function doubleUnderscorePatternHandlesMultilineStrings(): void
    {
        $doubleUnderscorePattern = RegexExtractor::DOUBLE_UNDERSCORE_SYNTAX_PATTERN;

        $contents = <<<'TEXT'
__(
    'Multi-line translation with double underscore'
)
TEXT;
        preg_match_all($doubleUnderscorePattern, $contents, $matches);

        $this->assertCount(1, $matches[3]);
        $this->assertEquals('Multi-line translation with double underscore', $matches[3][0]);
    }

    #[Test]
    public function doubleUnderscorePatternHandlesMultilineStringsInBladeAndPhp(): void
    {
        $doubleUnderscorePattern = RegexExtractor::DOUBLE_UNDERSCORE_SYNTAX_PATTERN;

        $bladeContent = <<<'TEXT'
<div>
    {{ __(
        'Multi-line Blade translation'
    ) }}
</div>
TEXT;

        $phpContent = <<<'TEXT'
<?php
echo __(
    'Multi-line PHP translation'
);
TEXT;

        preg_match_all($doubleUnderscorePattern, $bladeContent, $bladeMatches);
        preg_match_all($doubleUnderscorePattern, $phpContent, $phpMatches);

        $this->assertContains('Multi-line Blade translation', $bladeMatches[3]);
        $this->assertContains('Multi-line PHP translation', $phpMatches[3]);
    }

    #[Test]
    public function dollarUnderscorePatternHandlesMultilineStrings(): void
    {
        $dollarUnderscorePattern = RegexExtractor::DOLLAR_UNDERSCORE_PATTERN;

        $contents = <<<'TEXT'
$_(
    'Multi-line translation with dollar underscore'
)
TEXT;
        preg_match_all($dollarUnderscorePattern, $contents, $matches);

        $this->assertCount(1, $matches[1]);
        $this->assertEquals('Multi-line translation with dollar underscore', $matches[1][0]);
    }

    #[Test]
    public function dollarUnderscorePatternHandlesMultilineStringsInVueAndBlade(): void
    {
        $dollarUnderscorePattern = RegexExtractor::DOLLAR_UNDERSCORE_PATTERN;

        $vueContent = <<<'TEXT'
<template>
    <div>
        {{ $_(
            'Multi-line Vue translation with dollar underscore'
        ) }}
    </div>
</template>
TEXT;

        $bladeContent = <<<'TEXT'
<div>
    {{ $_(
        'Multi-line Blade translation with dollar underscore'
    ) }}
</div>
TEXT;

        preg_match_all($dollarUnderscorePattern, $vueContent, $vueMatches);
        preg_match_all($dollarUnderscorePattern, $bladeContent, $bladeMatches);

        $this->assertContains('Multi-line Vue translation with dollar underscore', $vueMatches[1]);
        $this->assertContains('Multi-line Blade translation with dollar underscore', $bladeMatches[1]);
    }
}
