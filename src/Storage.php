<?php

namespace Saleh7\Zatca;

use Saleh7\Zatca\Exceptions\ZatcaStorageException;

class Storage
{
    private static ?string $basePath = null;

    /**
     * Constructor to set base storage path.
     *
     * @param string|null $basePath Root directory for storage. Set to null if you want to handle files with a full path.
     */
    public function __construct(?string $basePath = null)
    {
        if ($basePath) {
            self::$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Sets a global base storage path.
     *
     * @param string $path The base directory.
     */
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Writes data to a file, creating directories if necessary.
     *
     * @param string $path Relative or full path of the file.
     * @param string $content Content to write.
     * @return bool True if writing was successful, false otherwise.
     * @throws ZatcaStorageException If the file cannot be written.
     */
    public function put(string $path, string $content): bool
    {
        $fullPath = $this->path($path);
        $directory = dirname($fullPath);

        $this->ensureDirectoryExists($directory);

        if (file_put_contents($fullPath, $content) === false) {
            throw new ZatcaStorageException("Failed to write to file.", [
                'path' => $fullPath,
            ]);
        }

        return true;
    }

    /**
     * Appends data to a file, creating directories if necessary.
     *
     * @param string $path Relative or full path of the file.
     * @param string $content Content to append.
     * @return bool True if writing was successful, false otherwise.
     * @throws ZatcaStorageException If the file cannot be written.
     */
    public function append(string $path, string $content): bool
    {
        $fullPath = $this->path($path);
        $directory = dirname($fullPath);

        $this->ensureDirectoryExists($directory);

        if (file_put_contents($fullPath, $content, FILE_APPEND) === false) {
            throw new ZatcaStorageException("Failed to append to file.", [
                'path' => $fullPath,
            ]);
        }

        return true;
    }

    /**
     * Reads content from a file.
     *
     * @param string $path Relative or full path of the file.
     * @return string The file contents.
     * @throws ZatcaStorageException If the file does not exist or cannot be read.
     */
    public function get(string $path): string
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            throw new ZatcaStorageException("File not found.", [
                'path' => $fullPath,
            ]);
        }

        $content = file_get_contents($fullPath);

        if ($content === false) {
            throw new ZatcaStorageException("Failed to read file.", [
                'path' => $fullPath,
            ]);
        }

        return $content;
    }

    /**
     * Checks if a file exists.
     *
     * @param string $path Relative or full path of the file.
     *
     * @return bool True if the file exists, false otherwise.
     * @throws ZatcaStorageException
     */
    public function exists(string $path): bool
    {
        return file_exists($this->path($path));
    }

    /**
     * Returns the full path of a file.
     *
     * @param string $file Relative or full path of the file.
     * @return string Absolute path to the file.
     * @throws ZatcaStorageException If basePath is not set.
     */
    public function path(string $file): string
    {
        if (!self::$basePath && !realpath($file)) {
            throw new ZatcaStorageException("Base path is not set, and no absolute path provided.", [
                'file' => $file,
            ]);
        }

        return self::$basePath ? self::$basePath . DIRECTORY_SEPARATOR . $file : $file;
    }

    /**
     * Ensures the directory exists, creates it if needed.
     *
     * @param string $path Directory path.
     * @throws ZatcaStorageException If the directory cannot be created.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!$path) {
            return;
        }

        // If directory exists but is not writable, throw exception
        if (is_dir($path) && !is_writable($path)) {
            throw new ZatcaStorageException('Directory exists but is not writable.', ['path' => $path]);
        }

        // If parent directory is not writable, fail before mkdir()
        $parentDir = dirname($path);
        if (!is_writable($parentDir)) {
            throw new ZatcaStorageException('Parent directory is not writable.', ['path' => $parentDir]);
        }

        // If directory does not exist, attempt to create it
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new ZatcaStorageException('Failed to create directory.', ['path' => $path]);
        }
    }
}