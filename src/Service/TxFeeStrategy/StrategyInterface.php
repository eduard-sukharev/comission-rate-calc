<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Model\TransactionsHistory;
use Money\Money;

interface StrategyInterface
{
    /**
     * @param Transaction $tx Transaction to calculate fee for
     * @param TransactionsHistory $txHistory History of transactions at least up to transaction of interest
     * @return ?Money Fee value, if any
     */
    public function calculateFee(Transaction $tx, TransactionsHistory $txHistory): ?Money;

    public function isSupported(Transaction $tx): bool;
}
