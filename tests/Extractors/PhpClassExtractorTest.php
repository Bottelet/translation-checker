<?php

namespace Tests\Unit\Extractor;

use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class PhpClassExtractorTest extends TestCase
{
    private string $tempDir;

    private SplFileInfo $phpControllerFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/php_class_extractor_tests';
        if (! file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $phpController = $this->tempDir . '/TestController.php';
        $phpControllerPath = 'translation-checker/tests/templates/TestController.php';
        file_put_contents($phpController, file_get_contents($phpControllerPath));

        $this->phpControllerFile = new SplFileInfo($phpController);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->tempDir}/*.*"));
        rmdir($this->tempDir);

        parent::tearDown();
    }

    #[Test]
    public function canFindFunctionsInController(): void
    {
        $phpExtractor = new PhpClassExtractor;
        $foundStrings = $phpExtractor->extractFromFile($this->phpControllerFile);

        $this->assertCount(10, $foundStrings);
    }
}
