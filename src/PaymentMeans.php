<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class PaymentMeans
 *
 * Represents payment means information for XML serialization.
 */
class PaymentMeans implements XmlSerializable
{
    /** @var string|null Payment means code. */
    private ?string $paymentMeansCode = null;

    /** @var string|null Instruction note. */
    private ?string $instructionNote = null;

    /**
     * @var mixed|null Payee financial account.
     * 
     * @note Consider replacing 'mixed' with a more specific type (e.g., FinancialAccount)
     *       if available.
     */
    private $payeeFinancialAccount = null;

    /** @var string|null Payment ID. */
    private ?string $paymentId = null;

    /**
     * Set the payment means code.
     *
     * @param string|null $paymentMeansCode
     * @return self
     * @throws InvalidArgumentException if an empty string is provided.
     */
    public function setPaymentMeansCode(?string $paymentMeansCode): self
    {
        if ($paymentMeansCode !== null && trim($paymentMeansCode) === '') {
            throw new InvalidArgumentException('Payment means code cannot be empty.');
        }
        $this->paymentMeansCode = $paymentMeansCode;
        return $this;
    }

    /**
     * Set the instruction note.
     *
     * @param string|null $instructionNote
     * @return self
     * @throws InvalidArgumentException if an empty string is provided.
     */
    public function setInstructionNote(?string $instructionNote): self
    {
        if ($instructionNote !== null && trim($instructionNote) === '') {
            throw new InvalidArgumentException('Instruction note cannot be empty.');
        }
        $this->instructionNote = $instructionNote;
        return $this;
    }

    /**
     * Set the payment ID.
     *
     * @param string|null $paymentId
     * @return self
     * @throws InvalidArgumentException if an empty string is provided.
     */
    public function setPaymentId(?string $paymentId): self
    {
        if ($paymentId !== null && trim($paymentId) === '') {
            throw new InvalidArgumentException('Payment ID cannot be empty.');
        }
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * Set the payee financial account.
     *
     * @param mixed $payeeFinancialAccount
     * @return self
     *
     * @note Consider using a more specific type than mixed.
     */
    public function setPayeeFinancialAccount($payeeFinancialAccount): self
    {
        $this->payeeFinancialAccount = $payeeFinancialAccount;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer
     * @return void
     */
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
