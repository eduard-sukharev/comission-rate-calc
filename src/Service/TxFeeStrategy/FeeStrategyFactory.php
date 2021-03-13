<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;

class FeeStrategyFactory
{
    public static function createStrategy(Transaction $tx)
    {
        if ($tx->isDeposit()) {
            return new FixedFeeStrategy(0.03);
        }
        if ($tx->isWithdraw()) {
            if ($tx->isClientBusiness()) {
                return new FixedFeeStrategy(0.5);
            }
        }

        // No defined rule found, assume no fee
        return new FixedFeeStrategy(0);
    }
}
