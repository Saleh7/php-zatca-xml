<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Mappers\Validators\InvoiceAmountValidator;

/**
 * Class InvoiceAmountValidatorTest
 *
 * This class tests the InvoiceAmountValidator to ensure that the monetary totals
 * and invoice lines amounts are calculated correctly and consistently.
 */
class InvoiceAmountValidatorTest extends TestCase
{
    /**
     * @var InvoiceAmountValidator
     */
    private InvoiceAmountValidator $validator;

    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        $this->validator = new InvoiceAmountValidator();
    }

    /**
     * Test that valid monetary totals and invoice line amounts pass validation.
     *
     * This test uses a sample invoice data array with valid legal monetary totals and
     * invoice line calculations. It asserts that no exceptions are thrown.
     */
    public function testValidAmounts(): void
    {
        $data = [
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => 200,
                'taxExclusiveAmount'  => 200,
                'taxInclusiveAmount'  => 230,
                'payableAmount'       => 230
            ],
            'taxTotal' => [
                'taxAmount' => 30,
                'subTotals' => [
                    [
                        'taxableAmount' => 200,
                        'taxAmount'     => 30,
                        'percent'       => 15,
                        'taxScheme'     => ['id' => 'VAT']
                    ]
                ]
            ],
            'invoiceLines' => [
                [
                    'id'                => 1,
                    'unitCode'          => 'PCE',
                    'quantity'          => 2,
                    'lineExtensionAmount'=> 200, // 100 * 2 = 200
                    'item' => [
                        'name'       => 'Product A',
                        'taxPercent' => 15,
                        'taxScheme'  => ['id' => 'VAT']
                    ],
                    'price' => [
                        'amount'   => 100,
                        'unitCode' => 'PCE'
                    ],
                    'taxTotal' => [
                        'taxAmount'     => 30,
                        'roundingAmount'=> 230  // 200 + 30 = 230
                    ]
                ]
            ]
        ];

        // Expect no exception during validation.
        $this->validator->validateMonetaryTotals($data);
        $this->validator->validateInvoiceLines($data['invoiceLines']);

        $this->assertTrue(true); // Test passes if no exception is thrown.
    }

    /**
     * Test that an incorrect lineExtensionAmount calculation throws an exception.
     *
     * For example, if price * quantity does not equal the provided lineExtensionAmount.
     */
    public function testInvalidLineExtensionCalculationThrowsException(): void
    {
        $data = [
            'invoiceLines' => [
                [
                    'id'                => 1,
                    'unitCode'          => 'PCE',
                    'quantity'          => 2,
                    // Incorrect lineExtensionAmount: expected 100 * 2 = 200, but provided 190
                    'lineExtensionAmount'=> 190,
                    'item' => [
                        'name'       => 'Product A',
                        'taxPercent' => 15,
                        'taxScheme'  => ['id' => 'VAT']
                    ],
                    'price' => [
                        'amount'   => 100,
                        'unitCode' => 'PCE'
                    ],
                    'taxTotal' => [
                        'taxAmount'     => 30,
                        'roundingAmount'=> 220 // 190 + 30 = 220
                    ]
                ]
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("lineExtensionAmount is incorrect");
        $this->validator->validateInvoiceLines($data['invoiceLines']);
    }

    /**
     * Test that an incorrect roundingAmount calculation throws an exception.
     *
     * For example, if roundingAmount does not equal lineExtensionAmount plus taxTotal.taxAmount.
     */
    public function testInvalidRoundingAmountCalculationThrowsException(): void
    {
        $data = [
            'invoiceLines' => [
                [
                    'id'                => 1,
                    'unitCode'          => 'PCE',
                    'quantity'          => 2,
                    'lineExtensionAmount'=> 200, // 100 * 2 = 200
                    'item' => [
                        'name'       => 'Product A',
                        'taxPercent' => 15,
                        'taxScheme'  => ['id' => 'VAT']
                    ],
                    'price' => [
                        'amount'   => 100,
                        'unitCode' => 'PCE'
                    ],
                    'taxTotal' => [
                        'taxAmount'     => 30,
                        // Incorrect roundingAmount: expected 200 + 30 = 230, but provided 225
                        'roundingAmount'=> 225
                    ]
                ]
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("roundingAmount is incorrect");
        $this->validator->validateInvoiceLines($data['invoiceLines']);
    }
}