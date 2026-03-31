<?php

namespace Saleh7\Zatca\Api;

/**
 * Base response class for ZATCA API responses.
 *
 * Wraps the decoded JSON response and provides convenient accessor methods.
 * All ZATCA API responses share common patterns: status codes, validation results,
 * and error/warning messages.
 */
class ApiResponse
{
    /** @var array The decoded response data */
    protected array $data;

    /** @var int HTTP status code */
    protected int $statusCode;

    public function __construct(array $data, int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the full raw response data.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get a value from the response by key.
     *
     * @param string $key     Response key
     * @param mixed  $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if the response indicates success (HTTP 200).
     */
    public function isSuccess(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * Check if the response indicates acceptance with warnings (HTTP 202).
     */
    public function hasWarnings(): bool
    {
        return $this->statusCode === 202
            || !empty($this->getWarningMessages());
    }

    /**
     * Check if the response contains errors.
     */
    public function hasErrors(): bool
    {
        return !empty($this->getErrorMessages());
    }

    /**
     * Get validation results if present.
     */
    public function getValidationResults(): array
    {
        return $this->get('validationResults', []);
    }

    /**
     * Get the overall validation status.
     *
     * @return string|null "PASS", "WARNING", or "ERROR"
     */
    public function getValidationStatus(): ?string
    {
        return $this->getValidationResults()['status'] ?? null;
    }

    /**
     * Get info messages from validation results.
     */
    public function getInfoMessages(): array
    {
        return $this->getValidationResults()['infoMessages'] ?? [];
    }

    /**
     * Get warning messages from validation results.
     */
    public function getWarningMessages(): array
    {
        return $this->getValidationResults()['warningMessages'] ?? [];
    }

    /**
     * Get error messages from validation results.
     */
    public function getErrorMessages(): array
    {
        return $this->getValidationResults()['errorMessages'] ?? [];
    }
}
