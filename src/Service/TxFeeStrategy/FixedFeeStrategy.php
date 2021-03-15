<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Model\TransactionsHistory;
use Money\Money;

abstract class FixedFeeStrategy implements StrategyInterface
{
    private float $feeRate;

    public function __construct(float $feePercent)
    {
        $this->feeRate = $feePercent;
    }

    /**
     * @inheritDoc
     */
    public function calculateFee(Transaction $tx, TransactionsHistory $txHistory): Money
    {
        return $tx->getValue()->multiply($this->feeRate / 100);
    }
}
