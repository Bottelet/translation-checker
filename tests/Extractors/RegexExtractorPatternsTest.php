<?php

namespace Bottelet\TranslationChecker\Extractor;

use PHPUnit\Framework\TestCase;

class RegexExtractorPatternsTest extends TestCase
{
    /**
     * @test
     */
    public function doubleUnderscorePattern(): void
    {
        $doubleUnderscorePattern = '/(__\()([\'"])(.*?)\2/';
        $contents = "{{ __('welcome') }} lorem ipsum {!! __('user') !! }}";
        preg_match_all($doubleUnderscorePattern, $contents, $matches);

        $this->assertEquals(['welcome', 'user'], $matches[3]);
    }

    /**
     * @test
     */
    public function doubleUnderscorePatternWithVariables(): void
    {
        $doubleUnderscorePattern = '/(__\()([\'"])(.*?)\2/';
        $contents = "some random text __('case with :key', [key => 'key']) more random text __('another_case', 'test')";
        preg_match_all($doubleUnderscorePattern, $contents, $matches);

        $this->assertContains('case with :key', $matches[3]);
        $this->assertContains('another_case', $matches[3]);
        $this->assertNotContains('more random text', $matches[3]);
    }

    /**
     * @test
     */
    public function tPattern(): void
    {
        $tPattern = '/\$?t\([\'"]([^\'"]+)[\'"]\)/';

        $contents = "<template>{{ \$t('welcome_t') }} not me {{ \$t('Hello t world') }}  {{ t('only t in bracket') }} t('only t withoutbrackets')</template>";
        preg_match_all($tPattern, $contents, $matches);

        $this->assertContains('Hello t world', $matches[1]);
        $this->assertCount(4, $matches[1]);
    }

    /**
     * @test
     */
    public function tPatternAdditional(): void
    {
        $tPattern = '/\$?t\([\'"]([^\'"]+)[\'"]\)/';

        $contents = "let text = t('vue_case'); let anotherText = \$t('dollar_case');";
        preg_match_all($tPattern, $contents, $matches);

        $this->assertEquals(['vue_case', 'dollar_case'], $matches[1]);
    }

    /**
     * @test
     */
    public function dollarUnderscorePattern(): void
    {
        $dollarUnderscorePattern = '/\$_\([\'"]([^\'"]+)[\'"]\)/';
        $contents = "Some text {\$_('welcome_underscore')} and more text \$_('another_underscore')";
        preg_match_all($dollarUnderscorePattern, $contents, $matches);

        $this->assertEquals(['welcome_underscore', 'another_underscore'], $matches[1]);
    }

    /**
     * @test
     */
    public function dollarUnderscorePatternAdditional(): void
    {
        $dollarUnderscorePattern = '/\$_\([\'"]([^\'"]+)[\'"]\)/';
        $contents = "Initial text \$_('first_underscore_case'); Ending text {\$_('second_underscore_case')};";
        preg_match_all($dollarUnderscorePattern, $contents, $matches);

        $this->assertEquals(['first_underscore_case', 'second_underscore_case'], $matches[1]);
    }
}
