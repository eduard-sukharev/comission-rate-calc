<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Model\TransactionsHistory;
use Money\Converter;
use Money\Currency;
use Money\Money;

class WeeklyThresholdFeeStrategy implements StrategyInterface
{
    private float $feeRate;
    private Money $freeThreshold;
    private int $freeWithdrawals;
    private Converter $converter;

    public function __construct(float $feePercent, Money $freeThreshold, int $freeWithdrawals, Converter $converter)
    {
        $this->feeRate = $feePercent;
        $this->freeThreshold = $freeThreshold;
        $this->freeWithdrawals = $freeWithdrawals;
        $this->converter = $converter;
    }

    /**
     * @inheritDoc
     */
    public function calculateFee(Transaction $tx, TransactionsHistory $txHistory): Money
    {
        $previousTxsSameWeek = $txHistory->getSameWeekTransactions($tx->getDate())
            ->getTransactionsUpToDate($tx->getDate());
        foreach ($previousTxsSameWeek as $prevTx) {
            $prevTxValue = $prevTx->getValue();
            if (!$this->freeThreshold->isSameCurrency($prevTxValue)) {
                $prevTxValue = $this->converter->convert($prevTxValue, $this->freeThreshold->getCurrency());
            }
            $this->freeThreshold = $this->freeThreshold->subtract($prevTxValue);
            $this->freeWithdrawals--;
        }
        // No free withdrawals left
        if ($this->freeWithdrawals <= 0) {
            return $tx->getValue()->multiply($this->feeRate / 100);
        }
        // Threshold not reached
        if ($this->freeThreshold->isPositive()) {
            $txValue = $tx->getValue();
            if (!$this->freeThreshold->isSameCurrency($txValue)) {
                $txValue = $this->converter->convert($txValue, $this->freeThreshold->getCurrency());
            }
            $exceeds = $this->converter->convert(
                $txValue->subtract($this->freeThreshold),
                $tx->getValue()->getCurrency()
            );

            return $exceeds->multiply($this->feeRate / 100);
        }

        return $tx->getValue()->multiply($this->feeRate / 100);
    }
}
