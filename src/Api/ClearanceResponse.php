<?php

namespace Saleh7\Zatca\Api;

/**
 * Response for clearance invoice submissions (B2B / Standard invoices).
 *
 * ZATCA endpoint: POST /invoices/clearance/single
 *
 * Successful clearance returns clearanceStatus = "CLEARED" and includes
 * the cleared (stamped) invoice XML in the response.
 */
class ClearanceResponse extends ApiResponse
{
    /**
     * Check if the invoice was successfully cleared.
     */
    public function isCleared(): bool
    {
        return $this->getClearanceStatus() === 'CLEARED';
    }

    /**
     * Get the clearance status.
     *
     * @return string|null "CLEARED", "NOT_CLEARED", or null
     */
    public function getClearanceStatus(): ?string
    {
        return $this->get('clearanceStatus');
    }

    /**
     * Get the cleared (stamped) invoice returned by ZATCA.
     *
     * This is the base64-encoded XML invoice with ZATCA's stamp applied.
     * Only present when clearance is successful.
     *
     * @return string|null Base64-encoded cleared invoice XML
     */
    public function getClearedInvoice(): ?string
    {
        return $this->get('clearedInvoice');
    }

    /**
     * Get the cleared invoice decoded from base64.
     *
     * @return string|null Decoded XML string
     */
    public function getDecodedClearedInvoice(): ?string
    {
        $encoded = $this->getClearedInvoice();
        if ($encoded === null) {
            return null;
        }
        $decoded = base64_decode($encoded, true);
        return $decoded !== false ? $decoded : null;
    }
}
