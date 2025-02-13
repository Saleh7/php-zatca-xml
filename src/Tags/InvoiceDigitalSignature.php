<?php

namespace Saleh7\Zatca\Tags;

use Saleh7\Zatca\Tag;

class InvoiceDigitalSignature extends Tag
{
    public function __construct($value)
    {
        parent::__construct(7, $value);
    }
}
