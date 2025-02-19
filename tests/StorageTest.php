<?php

namespace Saleh7\Zatca\Tests;

use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Storage;
use Saleh7\Zatca\Exceptions\ZatcaStorageException;

class StorageTest extends TestCase
{
    private string $testDir = __DIR__ . '/test_storage';
    private string $testFile = 'test.txt';
    private Storage $storage;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        // Set test storage directory
        Storage::setBasePath($this->testDir);
        $this->storage = new Storage();

        // Remove test directory before each test
        if (is_dir($this->testDir)) {
            $this->deleteDirectory($this->testDir);
        }
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        // Remove test directory after each test
        if (is_dir($this->testDir)) {
            $this->deleteDirectory($this->testDir);
        }
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir Directory path.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($filePath) ? $this->deleteDirectory($filePath) : unlink($filePath);
        }
        rmdir($dir);
    }

    /**
     * Test file writing and reading.
     */
    public function testPutAndGet(): void
    {
        $content = "Hello, PHPUnit!";
        $this->storage->put($this->testFile, $content);

        $this->assertFileExists($this->storage->path($this->testFile));
        $this->assertSame($content, $this->storage->get($this->testFile));
    }

    /**
     * Test appending data to a file.
     */
    public function testAppend(): void
    {
        $this->storage->put($this->testFile, "Line 1");
        $this->storage->append($this->testFile, "\nLine 2");

        $expectedContent = "Line 1\nLine 2";
        $this->assertSame($expectedContent, $this->storage->get($this->testFile));
    }

    /**
     * Test file existence check.
     */
    public function testExists(): void
    {
        $this->assertFalse($this->storage->exists($this->testFile));

        $this->storage->put($this->testFile, "Test content");
        $this->assertTrue($this->storage->exists($this->testFile));
    }

    /**
     * Test path generation.
     */
    public function testPath(): void
    {
        $expectedPath = $this->testDir . DIRECTORY_SEPARATOR . $this->testFile;
        $this->assertSame($expectedPath, $this->storage->path($this->testFile));
    }

    /**
     * Test setting and using a global base path.
     */
    public function testSetBasePath(): void
    {
        $newPath = __DIR__ . '/new_storage';
        Storage::setBasePath($newPath);
        $storage = new Storage();

        $this->assertSame($newPath . DIRECTORY_SEPARATOR . $this->testFile, $storage->path($this->testFile));
    }

    /**
     * Test exception when reading a non-existent file.
     */
    public function testGetThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(ZatcaStorageException::class);
        $this->expectExceptionMessage("File not found.");

        $this->storage->get('non_existent.txt');
    }

    /**
     * Test exception when writing to a non-writable directory.
     */
    public function testPutThrowsExceptionOnFailure(): void
    {
        $this->expectException(ZatcaStorageException::class);
        $this->expectExceptionMessage("Directory exists but is not writable.");

        // Ensure test directory does not exist
        $unwritableDir = sys_get_temp_dir() . '/unwritable_dir';
        if (is_dir($unwritableDir)) {
            chmod($unwritableDir, 0755);
            rmdir($unwritableDir);
        }

        // Create read-only directory
        mkdir($unwritableDir, 0444); // Read-only

        try {
            $storage = new Storage($unwritableDir);
            $storage->put('test.txt', 'Should fail');
        } finally {
            chmod($unwritableDir, 0755); // Restore permissions before deleting
            rmdir($unwritableDir);
        }
    }

    /**
     * Test exception when trying to create a directory in a read-only filesystem.
     */
    public function testEnsureDirectoryExistsThrowsExceptionOnFailure(): void
    {
        $this->expectException(ZatcaStorageException::class);
        $this->expectExceptionMessage("Parent directory is not writable.");

        // Ensure test directory does not exist
        $unwritableDir = sys_get_temp_dir() . '/unwritable_dir';
        if (is_dir($unwritableDir)) {
            chmod($unwritableDir, 0755);
            rmdir($unwritableDir);
        }

        // Create read-only directory
        mkdir($unwritableDir, 0444); // Read-only

        try {
            $storage = new Storage($unwritableDir);
            $storage->put('subdir/test.txt', 'Should fail');
        } finally {
            chmod($unwritableDir, 0755); // Restore permissions before deleting
            rmdir($unwritableDir);
        }
    }
}