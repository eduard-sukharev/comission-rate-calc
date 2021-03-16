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
        $freeWithdrawals = $this->freeWithdrawals;
        $freeThreshold = $this->freeThreshold;
        $previousTxsSameWeek = $txHistory->filterBySameWeek($tx->getDate())
            ->filterAllBeforeTx($tx)
            ->filterByClient($tx->getClientId())
            ->filterByFeeStrategySupport($this);
        foreach ($previousTxsSameWeek as $prevTx) {
            $prevTxValue = $prevTx->getValue();
            if (!$freeThreshold->isSameCurrency($prevTxValue)) {
                $prevTxValue = $this->exchangeConverter->convert(
                    $prevTxValue,
                    $freeThreshold->getCurrency(),
                    $prevTx->getDate()
                );
            }
            $freeThreshold = $freeThreshold->subtract($prevTxValue);
            $freeWithdrawals--;
        }
        // No free withdrawals left
        if ($freeWithdrawals <= 0) {
            return $tx->getValue()->multiply($this->feeRate, Money::ROUND_UP);
        }
        // Threshold not reached
        if (!$freeThreshold->isPositive()) {
            // threshold exceeded, everything is subject to fees
            return $tx->getValue()->multiply($this->feeRate, Money::ROUND_UP);
        }

        $txValue = $tx->getValue();
        if (!$freeThreshold->isSameCurrency($txValue)) {
            $txValue = $this->exchangeConverter->convert($txValue, $freeThreshold->getCurrency(), $tx->getDate());
        }

        if ($txValue->lessThanOrEqual($freeThreshold)) {
            return new Money(0, $tx->getValue()->getCurrency());
        }

        $txOverdraft = $txValue->subtract($freeThreshold);
        // Threshold Exceeds in original tx currency
        if (!$txOverdraft->isSameCurrency($tx->getValue())) {
            $txOverdraft = $this->exchangeConverter->convert(
                $txOverdraft,
                $tx->getValue()->getCurrency(),
                $tx->getDate()
            );
        }

        return $txOverdraft->multiply($this->feeRate, Money::ROUND_UP);
    }

    public function isSupported(Transaction $tx): bool
    {
        return $tx->isWithdraw() && $tx->isClientPrivate();
    }
}
