<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

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
        TEXT;

        file_put_contents($this->tempDir . '/test.php', $testPhpContent);

        $file = new SplFileInfo($this->tempDir . '/test.php');
        $extractor = new RegexExtractor;

        $extractor->addPattern('/mytranslatefunction\((["\'])(.*?)\1\)/', 2, 'mytranslatefunction');

        $translationKeys = $extractor->extractFromFile($file);

        $this->assertCount(1, $translationKeys);
        $this->assertContains('simple_string', $translationKeys);
    }
}
