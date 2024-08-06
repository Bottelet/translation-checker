<?php

namespace Bottelet\TranslationChecker\Tests;

use Bottelet\TranslationChecker\FileManagement;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileManagementTest extends \Bottelet\TranslationChecker\Tests\TestCase
{
    private $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary directory for testing
        $this->testDir = sys_get_temp_dir() . '/translation_checker_tests';
        if (! file_exists($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }

        $nestedDir = $this->testDir . '/nested';
        $deepNestedDir = $this->testDir . '/nested/deep/deeper';
        $crazyDeep = $this->testDir . '/nested/deep/deeper/deepest/darkness/stop';
        mkdir($crazyDeep, 0777, true);

        file_put_contents($deepNestedDir . '/exampleDeep1.php', "<?php echo 'Deeply Nested Hello';");
        file_put_contents($crazyDeep . '/exampleDeep2.php', "<?php echo 'Deeply Nested World';");

        file_put_contents($this->testDir . '/example1.php', "<?php echo 'Hello World';");
        file_put_contents($nestedDir . '/example2.php', "<?php echo 'Hello Again';");
    }

    protected function tearDown(): void
    {
        // Recursively remove the testing directory
        $it = new RecursiveDirectoryIterator($this->testDir, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->testDir);

        parent::tearDown();
    }

    #[Test]
    public function getAllFilesReturnsArray()
    {
        $fileManagement = new FileManagement;
        $files = $fileManagement->getAllFiles([$this->testDir]);
        $this->assertIsArray($files);
    }

    #[Test]
    public function getAllFiles()
    {
        $fileManagement = new FileManagement;
        $files = $fileManagement->getAllFiles([$this->testDir]);
        $this->assertCount(4, $files); // Expecting 2 files we created
        foreach ($files as $file) {
            $this->assertStringEndsWith('.php', $file);
        }
    }

    #[Test]
    public function getAllFilesExcludesDirectories()
    {
        $fileManagement = new FileManagement;
        $files = $fileManagement->getAllFiles([$this->testDir]);
        foreach ($files as $file) {
            $this->assertFileExists($file);
            $this->assertFalse(is_dir($file));
        }
    }

    #[Test]
    public function nonexistentPathReturnsEmptyArray()
    {
        $fileManagement = new FileManagement;
        $files = $fileManagement->getAllFiles(['/nonexistent/path']);
        $this->assertEmpty($files);
    }

    #[Test]
    public function pathNormalization()
    {
        $fileManagement = new FileManagement;
        // Adding multiple slashes to see if the method normalizes the path
        $files = $fileManagement->getAllFiles([$this->testDir . '///']);
        $this->assertNotEmpty($files);
    }

    #[Test]
    public function getAllFilesFindsDeeplyNestedFiles()
    {
        $fileManagement = new FileManagement;
        $files = $fileManagement->getAllFiles([$this->testDir]);

        // Check that deeply nested files are included
        $foundDeepFiles = array_filter($files, function ($file) {
            return strpos($file, 'exampleDeep') !== false;
        });

        $this->assertCount(2, $foundDeepFiles, 'Deeply nested files were not all found.');
    }
}
