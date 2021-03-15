<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Service\HistoryConverter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

class PrivateWithdrawFeeStrategy extends WeeklyThresholdFeeStrategy
{
    public function __construct(
        HistoryConverter $converter
    ) {
        $isoCurrencies = new ISOCurrencies();
        $currency = new Currency('EUR');
        $currencyScale = (10 ** $isoCurrencies->subunitFor($currency));
        parent::__construct(0.3, new Money(1000 * $currencyScale, $currency), 3, $converter);
    }

    public function isSupported(Transaction $tx): bool
    {
        return $tx->isWithdraw() && $tx->isClientPrivate();
    }
}
