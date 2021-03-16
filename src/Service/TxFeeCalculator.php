<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\TransactionsHistory;
use App\Service\TxFeeStrategy\DependencyInjection\TxFeeStrategyChain;
use Money\Money;

class TxFeeCalculator
{
    private TxFeeStrategyChain $feeStrategyChain;

    public function __construct(TxFeeStrategyChain $feeStrategyChain)
    {
        $this->feeStrategyChain = $feeStrategyChain;
    }

    public function calculateTxFees(TransactionsHistory $history): TransactionsHistory
    {
        foreach ($history as $tx) {
            $fee = new Money(0, $tx->getValue()->getCurrency());
            foreach ($this->feeStrategyChain->getStrategy() as $strategy) {
                if ($strategy->isSupported($tx)) {
                    $fee = $strategy->calculateFee($tx, $history);
                    break;
                }
            }
            $tx->setFee($fee);
        }

        return $history;
    }
}
