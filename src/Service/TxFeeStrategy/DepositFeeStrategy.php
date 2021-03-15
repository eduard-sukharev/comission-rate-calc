<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;

class DepositFeeStrategy extends FixedFeeStrategy
{
    public function __construct()
    {
        parent::__construct(0.03);
    }

    public function isSupported(Transaction $tx): bool
    {
        return $tx->isDeposit();
    }
}
