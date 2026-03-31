<?php

namespace Saleh7\Zatca\Api;

/**
 * Response for compliance invoice validation.
 *
 * ZATCA endpoint: POST /compliance/invoices
 *
 * Used during the compliance check phase to validate invoices before
 * requesting production certificates.
 */
class ComplianceInvoiceResponse extends ApiResponse
{
    /**
     * Get the reporting status (for simplified invoices).
     *
     * @return string|null "REPORTED", "NOT_REPORTED", or null
     */
    public function getReportingStatus(): ?string
    {
        return $this->get('reportingStatus');
    }

    /**
     * Get the clearance status (for standard invoices).
     *
     * @return string|null "CLEARED", "NOT_CLEARED", or null
     */
    public function getClearanceStatus(): ?string
    {
        return $this->get('clearanceStatus');
    }

    /**
     * Get the compliance status.
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->get('status');
    }
}
