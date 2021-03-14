<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Service\ExchangeConverter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

class FeeStrategyFactory
{
    private ExchangeConverter $exchangeConverter;

    public function __construct(ExchangeConverter $exchangeConverter)
    {
        $this->exchangeConverter = $exchangeConverter;
    }

    public function createStrategy(Transaction $tx)
    {
        if ($tx->isDeposit()) {
            return new FixedFeeStrategy(0.03);
        }
        if ($tx->isWithdraw()) {
            if ($tx->isClientBusiness()) {
                return new FixedFeeStrategy(0.5);
            }
            if ($tx->isClientPrivate()) {
                $isoCurrencies = new ISOCurrencies();
                $currency = new Currency('EUR');
                $currencyScale = (10 ** $isoCurrencies->subunitFor($currency));
                return new WeeklyThresholdFeeStrategy(
                    0.3,
                    new Money(1000 * $currencyScale, $currency),
                    3,
                    $this->exchangeConverter
                );
            }
        }

        // No defined rule found, assume no fee
        return new FixedFeeStrategy(0);
    }
}
