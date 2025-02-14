<?php

namespace Saleh7\Zatca\Contracts;

use DateTime;
use Saleh7\Zatca\Address;
use Saleh7\Zatca\InvoiceType;
use Saleh7\Zatca\LegalMonetaryTotal;
use Saleh7\Zatca\Party;
use Saleh7\Zatca\TaxTotal;

/**
 * Interface ZatcaOrderInterface
 *
 * This interface defines the contract for a ZATCA order, including invoice, delivery,
 * payment, tax, and party information.
 */
interface ZatcaOrderContract
{
    /**
     * Get the order ID.
     *
     * @return string The unique order identifier.
     */
    public function getId(): string;

    /**
     * Get the UUID.
     *
     * @return string The UUID of the order.
     */
    public function getUuid(): string;

    /**
     * Get the currency code used in the order.
     *
     * @return string The currency code (e.g., "SAR", "USD").
     */
    public function getCurrency(): string;

    /**
     * Get the invoice type for the order.
     *
     * @return InvoiceType The type of invoice.
     */
    public function getInvoiceType(): InvoiceType;

    /**
     * Get the date and time when the order was issued.
     *
     * @return DateTime The issuance date and time.
     */
    public function getIssuedAt(): DateTime;

    /**
     * Get the actual delivery date of the order.
     *
     * @return DateTime|null The actual delivery date, if available.
     */
    public function getDeliveredAt(): ?DateTime;

    /**
     * Get the preferred delivery date for the order.
     *
     * @return DateTime|null The preferred delivery date, if specified.
     */
    public function getPreferredDeliveryDate(): ?DateTime;

    /**
     * Get the delivery address associated with the order.
     *
     * @return Address|null The delivery address, if provided.
     */
    public function getDeliveryAddress(): ?Address;

    /**
     * Get the payment type for the order.
     *
     * @return string The payment type.
     * - 10: cash
     * - 20: cheque
     * - 30: credit card
     * - 40: bank transfer
     * - 50: direct debit
     */
    public function getPaymentType(): string;

    /**
     * Get the total tax details for the order.
     *
     * @return TaxTotal The total tax information.
     */
    public function getTaxTotal(): TaxTotal;

    /**
     * Get the invoice line items for the order.
     *
     * @return array An array of invoice line items.
     */
    public function getInvoiceLines(): array;

    /**
     * Get the additional document references associated with the order.
     *
     * @return array An array of additional document references.
     */
    public function getAdditionalDocumentReferences(): array;

    /**
     * Get the allowance charges (discounts or surcharges) applied to the order.
     *
     * @return array An array of allowance charges.
     */
    public function getAllowanceCharges(): array;

    /**
     * Get the legal monetary totals for the order.
     *
     * @return LegalMonetaryTotal The legal monetary total, including sums like tax, prepayments, etc.
     */
    public function getLegalMonetaryTotal(): LegalMonetaryTotal;

    /**
     * Get the seller's (supplier's) information.
     *
     * @return Party The party representing the seller.
     */
    public function getSeller(): Party;

    /**
     * Get the customer's information.
     *
     * @return Party The party representing the customer.
     */
    public function getCustomer(): Party;
}