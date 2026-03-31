<?php

namespace Saleh7\Zatca\Api;

/**
 * Response for reporting invoice submissions (B2C / Simplified invoices).
 *
 * ZATCA endpoint: POST /invoices/reporting/single
 *
 * Successful reporting returns reportingStatus = "REPORTED".
 */
class ReportingResponse extends ApiResponse
{
    /**
     * Check if the invoice was successfully reported.
     */
    public function isReported(): bool
    {
        return $this->getReportingStatus() === 'REPORTED';
    }

    /**
     * Get the reporting status.
     *
     * @return string|null "REPORTED", "NOT_REPORTED", or null
     */
    public function getReportingStatus(): ?string
    {
        return $this->get('reportingStatus');
    }
}
