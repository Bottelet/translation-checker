<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\PhpBaseClassExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;

class PhpBaseClassExtractorTest extends TestCase
{
    #[Test]
    public function emptyOnWrongFileType(): void
    {
        $phpExtractor = new PhpBaseClassExtractor();
        $foundStrings = $phpExtractor->extractFromFile($this->vueFile);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function extractFileIsEmptyOnEmptyFile(): void
    {
        $phpExtractor = new PhpBaseClassExtractor();
        $foundStrings = $phpExtractor->extractFromFile($this->vueFile);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function emptyOnNonExistingFiles(): void
    {
        $filePath = $this->tempDir . '/non-existing.php';
        $file = new SplFileInfo($filePath);
        $phpExtractor = new PhpBaseClassExtractor();
        $foundStrings = $phpExtractor->extractFromFile($file);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function emptyFile(): void
    {
        $emptyFile = $this->createTempFile('empty.php', '<?php');
        $phpExtractor = new PhpBaseClassExtractor();
        $foundStrings = $phpExtractor->extractFromFile($emptyFile);

        $this->assertEmpty($foundStrings);
    }

    #[Test]
    public function getEmptyIfFilePermissionFails(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Skipping permission test on Windows');
            return;
        }

        $filePath = $this->tempDir . '/permission.php';
        file_put_contents($filePath, $this->phpControllerFile);

        chmod($filePath, 0200);

        $file = new SplFileInfo($filePath);
        $phpExtractor = new PhpBaseClassExtractor();
        $this->expectException(\ErrorException::class);
        $phpExtractor->extractFromFile($file);
    }
}
