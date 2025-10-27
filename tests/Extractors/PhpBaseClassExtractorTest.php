<?php

namespace Bottelet\TranslationChecker\Tests\Extractors;

use Bottelet\TranslationChecker\Extractor\PhpBaseClassExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
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
        $filePath = $this->tempDir . '/permission.php';
        file_put_contents($filePath, $this->phpControllerFile);

        chmod($filePath, 0200);

        $file = new SplFileInfo($filePath);
        $phpExtractor = new PhpBaseClassExtractor();
        $this->expectException(\ErrorException::class);
        $phpExtractor->extractFromFile($file);
    }

    #[Test]
    public function throwsExceptionWhenPhp84SyntaxUsedWithPhp82Parser(): void
    {
        config(['translator.php_version' => '8.2']);

        $phpCode = <<<'PHP'
<?php

class TestClass
{
    public private(set) ?string $title = null;
}
PHP;

        $file = $this->createTempFile('php84-syntax.php', $phpCode);
        $phpExtractor = new PhpBaseClassExtractor();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Multiple access type modifiers are not allowed/');
        $phpExtractor->extractFromFile($file);
    }

    #[Test]
    public function parsesPhp84SyntaxSuccessfullyWithPhp84Parser(): void
    {
        config(['translator.php_version' => '8.4']);

        $phpCode = <<<'PHP'
<?php

class TestClass
{
    public private(set) ?string $title = null;
}
PHP;

        $file = $this->createTempFile('php84-syntax-valid.php', $phpCode);
        $phpExtractor = new PhpBaseClassExtractor();

        $foundStrings = $phpExtractor->extractFromFile($file);

        $this->assertIsArray($foundStrings);
    }
}
