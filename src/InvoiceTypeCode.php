<?php
namespace Saleh7\Zatca;

class InvoiceTypeCode
{
    const INVOICE = 388; // Tax invoice
    const TAX_INVOICE = '0100000'; //  01 for tax invoice
    const SIMPLIFIED_INVOICE = '0200000'; // 02 for simplified tax invoice

    const DEBIT_NOTE = 383; // Debit note
    const TAX_DEBIT_NOTE = '0100000'; // For tax invoice debit note, code is 383 and subtype is 01.
    const SIMPLIFIED_DEBIT_NOTE = '0200000'; // For tax invoice debit note, code is 383 and subtype is 01.

    const CREDIT_NOTE = 381; // Credit note
    const TAX_CREDIT_NOTE = '0100000'; // For tax invoice credit note, code is 381 and subtype is 01.
    const SIMPLIFIED_CREDIT_NOTE = '0200000'; // For simplified credit note, code is 381 and subtype is 02.
}
