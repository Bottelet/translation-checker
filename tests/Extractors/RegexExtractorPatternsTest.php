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
}
