<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\TransactionsHistory;
use App\Service\TxFeeStrategy\FeeStrategyFactory;

class TxFeeCalculator
{
    private FeeStrategyFactory $strategyFactory;

    public function __construct(FeeStrategyFactory $strategyFactory)
    {
        $this->strategyFactory = $strategyFactory;
    }

    public function calculateTxFees(TransactionsHistory $history): TransactionsHistory
    {
        foreach ($history as $tx) {
            $tx->setFee($this->strategyFactory->createStrategy($tx)->calculateFee($tx, $history));
        }

        return $history;
    }
}
