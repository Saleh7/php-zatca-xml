<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class PaymentMeans implements XmlSerializable
{
    private $paymentMeansCode;
    private $instructionNote;
    private $payeeFinancialAccount;
    private $paymentId;

    /**
     * @param string $instructionId
     * @return PaymentMeans
     */
    public function setPaymentMeansCode(?string $paymentMeansCode): PaymentMeans
    {
        $this->paymentMeansCode = $paymentMeansCode;
        return $this;
    }

    /**
     * @param string $instructionNote
     * @return PaymentMeans
     */
    public function setInstructionNote(?string $instructionNote): PaymentMeans
    {
        $this->instructionNote = $instructionNote;
        return $this;
    }

    /**
     * @param string $paymentId
     * @return PaymentMeans
     */
    public function setPaymentId(?string $paymentId): PaymentMeans
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * @param mixed $payeeFinancialAccount
     * @return PaymentMeans
     */
    public function setPayeeFinancialAccount(PaymentMeans $payeeFinancialAccount): PaymentMeans
    {
        $this->payeeFinancialAccount = $payeeFinancialAccount;
        return $this;
    }

    public function xmlSerialize(Writer $writer): void
    {
        $writer->write([
            Schema::CBC . 'PaymentMeansCode' => $this->paymentMeansCode
        ]);

        if ($this->instructionNote !== null) {
            $writer->write([
                Schema::CBC . 'InstructionNote' => $this->instructionNote
            ]);
        }

        if ($this->paymentId !== null) {
            $writer->write([
                Schema::CBC . 'PaymentID' => $this->paymentId
            ]);
        }

        if ($this->payeeFinancialAccount !== null) {
            $writer->write([
                Schema::CAC . 'PayeeFinancialAccount' => $this->payeeFinancialAccount
            ]);
        }
    }
}
