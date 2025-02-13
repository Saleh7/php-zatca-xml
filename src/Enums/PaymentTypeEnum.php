<?php

namespace Saleh7\Zatca\Enums;

// todo make sure values are correct.
// todo documentation
enum PaymentTypeEnum: string
{
    case Cash = '10';

    case Cheque = '20';

    case CreditCard = '30';

    case BankTransfer = '40';

    case DirectDebit = '50';
}
