<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\RegexExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
        $t('dollar t');
        t("t translation")
        $_('underscore translator')
        { $t("dollar t inside curly bracket") }
        {{ $_('underscore translator inside curly bracket') }}
        {{ t("t translation inside curly brakcets") }}
        TEXT;

        file_put_contents($this->tempDir . '/test.php', $testPhpContent);
    }

    #[Test]
    public function it_correctly_extracts_translation_strings_from_php_file(): void
    {
        $file = new SplFileInfo($this->tempDir . '/test.php');
        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertCount(10, $translationKeys);
        $this->assertContains('with :variable test', $translationKeys);
        $this->assertContains('dollar t inside curly bracket', $translationKeys);
        $this->assertContains('simple_string', $translationKeys);
        $this->assertContains('underscore translator inside curly bracket', $translationKeys);
    }

    #[Test]
    public function it_handles_files_without_translation_strings(): void
    {
        $emptyFile = $this->tempDir . '/empty.php';
        file_put_contents($emptyFile, '<?php // No translations here');

        $file = new SplFileInfo($emptyFile);
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
        $jsFile = $this->tempDir . '/file.js';
        file_put_contents($jsFile, '<template>this.$emit(\'emit\'); this.request(\'request\'); $t(\'findable\') const target = target("target") </template>');

        $file = new SplFileInfo($jsFile);

        $extractor = new RegexExtractor;

        $translationKeys = $extractor->extractFromFile($file);
        $this->assertContains('findable', $translationKeys);
        $this->assertNotContains('emit', $translationKeys);
        $this->assertNotContains('request', $translationKeys);
        $this->assertNotContains('target', $translationKeys);
    }
}
