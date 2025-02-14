<?php

namespace Saleh7\Zatca;

use Saleh7\Zatca\Contracts\ZatcaOrderContract;

/**
 * Class OrderInvoice
 *
 * This class converts a ZatcaOrderContract instance into an Invoice object.
 * It maps order details to the corresponding invoice fields according to ZATCA requirements.
 */
class OrderInvoice
{
    /**
     * @var ZatcaOrderContract The order data used to build the invoice.
     */
    protected ZatcaOrderContract $order;

    /**
     * @var Invoice The resulting invoice built from the order.
     */
    protected Invoice $invoice;

    /**
     * OrderInvoice constructor.
     *
     * @param ZatcaOrderContract $order The order to be converted into an invoice.
     */
    public function __construct(ZatcaOrderContract $order)
    {
        $this->order = $order;
        $this->invoice = (new Invoice())
            // Optionally set UBL extensions if required.
            // ->setUBLExtensions($ublExtensions)
            ->setUUID($this->order->getUuid())
            ->setId($this->order->getId())
            ->setIssueDate($this->order->getIssuedAt())
            ->setIssueTime($this->order->getIssuedAt())
            ->setInvoiceType($this->order->getInvoiceType())
            ->setInvoiceCurrencyCode($this->order->getCurrency())
            ->setTaxCurrencyCode($this->order->getCurrency())
            // todo
            // ->setOrderReference()
            // ->setBillingReferences()
            // ->setContract()
            ->setAdditionalDocumentReferences($this->order->getAdditionalDocumentReferences()) // Set additional document references.
            ->setAccountingSupplierParty($this->order->getSeller())
            ->setAccountingCustomerParty($this->order->getCustomer())
            ->setDelivery($this->getDelivery())
            ->setPaymentMeans($this->getPaymentMeans())
            ->setAllowanceCharges($this->order->getAllowanceCharges())
            ->setTaxTotal($this->order->getTaxTotal())
            ->setLegalMonetaryTotal($this->order->getLegalMonetaryTotal())
            ->setInvoiceLines($this->order->getInvoiceLines());
            // todo
//            ->setSignature($signature);
    }

    /**
     * Returns the built Invoice object.
     *
     * @return Invoice The invoice created from the order.
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    /**
     * Builds and returns a Delivery object if any delivery details are provided.
     *
     * If any of Actual Delivery Date, Preferred Delivery Date, or Delivery Address are set,
     * a Delivery object is created with those details.
     *
     * @return Delivery|null The Delivery object or null if no delivery information is provided.
     */
    protected function getDelivery(): ?Delivery
    {
        $address = $this->order->getDeliveryAddress();
        $deliveredAt = $this->order->getDeliveredAt();
        $preferredDate = $this->order->getPreferredDeliveryDate();

        if ($deliveredAt || $preferredDate || $address) {
            return (new Delivery())
                ->setActualDeliveryDate($deliveredAt)
                ->setLatestDeliveryDate($preferredDate)
                ->setDeliveryLocation($address);
        }

        return null;
    }

    /**
     * Builds and returns a PaymentMeans object based on the order's payment type.
     *
     * @return PaymentMeans The PaymentMeans object containing payment details.
     */
    protected function getPaymentMeans(): PaymentMeans
    {
        return (new PaymentMeans())
            ->setPaymentMeansCode($this->order->getPaymentType()->value);
    }

    /**
     * Converts the built Invoice object to XML.
     *
     * @return string The XML string of the invoice.
     */
    public function toXML(): string
    {
        return GeneratorInvoice::invoice($this->invoice, $this->order->getCurrency());
    }
}