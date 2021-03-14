<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Model\TransactionsHistory;
use Money\Currency;
use Money\Exception\UnresolvableCurrencyPairException;
use Money\Money;
use Swap\Swap;

class WeeklyThresholdFeeStrategy implements StrategyInterface
{
    private float $feeRate;
    private Money $freeThreshold;
    private int $freeWithdrawals;
    private Swap $swap;

    public function __construct(float $feePercent, Money $freeThreshold, int $freeWithdrawals, Swap $swap)
    {
        $this->feeRate = $feePercent;
        $this->freeThreshold = $freeThreshold;
        $this->freeWithdrawals = $freeWithdrawals;
        $this->swap = $swap;
    }

    /**
     * @inheritDoc
     */
    public function calculateFee(Transaction $tx, TransactionsHistory $txHistory): ?Money
    {
        $previousTxsSameWeek = $txHistory->getSameWeekTransactions($tx->getDate())
            ->getTransactionsUpToDate($tx->getDate())
            ->getTransactionsForClient($tx->getClientId());
        foreach ($previousTxsSameWeek as $prevTx) {
            $prevTxValue = $prevTx->getValue();
            if (!$this->freeThreshold->isSameCurrency($prevTxValue)) {
                $prevTxValue = $this->convert($prevTxValue, $this->freeThreshold->getCurrency(), $prevTx->getDate());
            }
            $this->freeThreshold = $this->freeThreshold->subtract($prevTxValue);
            $this->freeWithdrawals--;
        }
//        echo 'TX: ' . json_encode($tx->getValue()) . PHP_EOL;
        // No free withdrawals left
        if ($this->freeWithdrawals <= 0) {
//            echo 'No free withdrawals left' . PHP_EOL;
            return $tx->getValue()->multiply($this->feeRate / 100);
        }
        // Threshold not reached
        if ($this->freeThreshold->isPositive()) {
//            echo 'Threshold not reached' . PHP_EOL;
            $txValue = $tx->getValue();
            if (!$this->freeThreshold->isSameCurrency($txValue)) {
                $txValue = $this->convert($txValue, $this->freeThreshold->getCurrency(), $tx->getDate());
            }
            $txThresholdExceeds = $txValue->subtract($this->freeThreshold);

            // Transaction value less than leftover threshold
            if (!$txThresholdExceeds->isPositive()) {
//                echo 'Transaction value less than leftover threshold' . PHP_EOL;
                return new Money(0, $tx->getValue()->getCurrency());
            }

            // Threshold Exceeds in original tx currency
            if (!$this->freeThreshold->isSameCurrency($txValue)) {
//                echo 'Threshold Exceeds in original tx currency' . PHP_EOL;
                $txThresholdExceeds = $this->convert(
                    $txThresholdExceeds,
                    $tx->getValue()->getCurrency(),
                    $tx->getDate()
                );
            }

//            echo 'Fee on Threshold exceeds' . PHP_EOL;
            return $txThresholdExceeds->multiply($this->feeRate / 100);
        }

        // threshold exceeded, everything is subject to fees
//        echo 'threshold exceeded, everything is subject to fees' . PHP_EOL;
        return $tx->getValue()->multiply($this->feeRate / 100);
    }

    private function convert(Money $money, Currency $currency, \DateTimeImmutable $date): Money
    {
        try {
            $rate = $this->swap->historical($money->getCurrency()->getCode() . '/' . $currency->getCode(), $date);
        } catch (UnresolvableCurrencyPairException $exception) {
            try {
                $inverseRate = $this->swap->historical(
                    $currency->getCode() . '/' . $money->getCurrency()->getCode(),
                    $date
                );

                return new Money($money->multiply(1 / $inverseRate->getValue())->getAmount(), $currency);
            } catch (UnresolvableCurrencyPairException $inversedException) {
                throw $exception;
            }
        }

        return new Money($money->multiply($rate->getValue())->getAmount(), $currency);
    }
}
