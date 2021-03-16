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
        $this->feeRate = $feePercent / 100;
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
            ->filterAllBeforeTx($tx)
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
            return $tx->getValue()->multiply($this->feeRate);
        }
        // Threshold not reached
        if (!$this->freeThreshold->isPositive()) {
            // threshold exceeded, everything is subject to fees
//        echo 'threshold exceeded, everything is subject to fees' . PHP_EOL;
            return $tx->getValue()->multiply($this->feeRate);
        }

        echo 'Threshold not reached' . PHP_EOL;
        $txValue = $tx->getValue();
        if (!$this->freeThreshold->isSameCurrency($txValue)) {
            $txValue = $this->exchangeConverter->convert($txValue, $this->freeThreshold->getCurrency(), $tx->getDate());
        }

        if ($txValue->lessThanOrEqual($this->freeThreshold)) {
            echo 'Transaction value less than leftover threshold' . PHP_EOL;
            return new Money(0, $tx->getValue()->getCurrency());
        }

        $txOverdraft = $txValue->subtract($this->freeThreshold);
        // Threshold Exceeds in original tx currency
        if (!$txOverdraft->isSameCurrency($tx->getValue())) {
            echo 'Overdraft not in original tx currency' . PHP_EOL;
            $txOverdraft = $this->exchangeConverter->convert(
                $txOverdraft,
                $tx->getValue()->getCurrency(),
                $tx->getDate()
            );
        }

        echo 'Fee on Threshold exceeds: ' . $txOverdraft->getAmount()
            . ' ' . $txOverdraft->getCurrency()->getCode() . PHP_EOL;
        return $txOverdraft->multiply($this->feeRate);
    }

    public function isSupported(Transaction $tx): bool
    {
        return $tx->isWithdraw() && $tx->isClientPrivate();
    }
}
