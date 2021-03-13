<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\TransactionsHistory;
use App\Service\TxFeeStrategy\FeeStrategyFactory;

class TxFeeCalculator
{
    public function calculateTxFees(TransactionsHistory $history): TransactionsHistory
    {
        foreach ($history as $tx) {
            $tx->setFee(FeeStrategyFactory::createStrategy($tx)->calculateFee($tx, $history));
        }

        return $history;
    }
}
