<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;

class NoFeeStrategy extends FixedFeeStrategy
{
    public function __construct()
    {
        parent::__construct(0);
    }

    public function isSupported(Transaction $tx): bool
    {
        return true;
    }
}
