<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Model\TransactionsHistory;
use App\Service\HistoryConverter;
use Money\Money;

abstract class WeeklyThresholdFeeStrategy implements StrategyInterface
{
    private float $feeRate;
    private Money $freeThreshold;
    private int $freeWithdrawals;
    private HistoryConverter $exchangeConverter;

    public function __construct(
        float $feePercent,
        Money $freeThreshold,
        int $freeWithdrawals,
        HistoryConverter $converter
    ) {
        $this->feeRate = $feePercent;
        $this->freeThreshold = $freeThreshold;
        $this->freeWithdrawals = $freeWithdrawals;
        $this->exchangeConverter = $converter;
    }

    /**
     * @inheritDoc
     */
    public function calculateFee(Transaction $tx, TransactionsHistory $txHistory): ?Money
    {
        $previousTxsSameWeek = $txHistory->filterBySameWeek($tx->getDate())
            ->filterUpToDate($tx->getDate())
            ->filterByFeeStrategySupport($this)
            ->filterByClient($tx->getClientId());
        foreach ($previousTxsSameWeek as $prevTx) {
            $prevTxValue = $prevTx->getValue();
            if (!$this->freeThreshold->isSameCurrency($prevTxValue)) {
                $prevTxValue = $this->exchangeConverter->convert(
                    $prevTxValue,
                    $this->freeThreshold->getCurrency(),
                    $prevTx->getDate()
                );
            }
            $this->freeThreshold = $this->freeThreshold->subtract($prevTxValue);
            $this->freeWithdrawals--;
        }
        echo 'TX: ' . json_encode($tx->getValue()) . PHP_EOL;
        // No free withdrawals left
        if ($this->freeWithdrawals <= 0) {
            echo 'No free withdrawals left' . PHP_EOL;
            return $tx->getValue()->multiply($this->feeRate / 100);
        }
        // Threshold not reached
        if (!$this->freeThreshold->isPositive()) {
            // threshold exceeded, everything is subject to fees
//        echo 'threshold exceeded, everything is subject to fees' . PHP_EOL;
            return $tx->getValue()->multiply($this->feeRate / 100);
        }

        echo 'Threshold not reached' . PHP_EOL;
        $txValue = $tx->getValue();
        if (!$this->freeThreshold->isSameCurrency($txValue)) {
            $txValue = $this->exchangeConverter->convert($txValue, $this->freeThreshold->getCurrency(), $tx->getDate());
        }
        $txThresholdExceeds = $txValue->subtract($this->freeThreshold);

        // Transaction value less than leftover threshold
        if (!$txThresholdExceeds->isPositive()) {
            echo 'Transaction value less than leftover threshold' . PHP_EOL;
            return new Money(0, $tx->getValue()->getCurrency());
        }

        // Threshold Exceeds in original tx currency
        if (!$this->freeThreshold->isSameCurrency($txValue)) {
            echo 'Threshold Exceeds in original tx currency' . PHP_EOL;
            $txThresholdExceeds = $this->exchangeConverter->convert(
                $txThresholdExceeds,
                $tx->getValue()->getCurrency(),
                $tx->getDate()
            );
        }

        echo 'Fee on Threshold exceeds: ' . $txThresholdExceeds->getAmount()
            . ' ' . $txThresholdExceeds->getCurrency()->getCode() . PHP_EOL;
        return $txThresholdExceeds->multiply($this->feeRate / 100);
    }

    public function isSupported(Transaction $tx): bool
    {
        return $tx->isWithdraw() && $tx->isClientPrivate();
    }
}
