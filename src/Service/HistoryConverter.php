<?php

declare(strict_types=1);

namespace App\Service;

use Exchanger\Contract\ExchangeRate;
use Exchanger\CurrencyPair;
use Money\Currencies;
use Money\Currency;
use Money\Exception\UnresolvableCurrencyPairException;
use Money\Money;
use Money\Number;
use Swap\Swap;

class HistoryConverter
{
    private Swap $swap;
    private Currencies $currencies;

    public function __construct(Currencies $currencies, Swap $swap)
    {
        $this->currencies = $currencies;
        $this->swap = $swap;
    }

    public function convert(
        Money $money,
        Currency $counterCurrency,
        \DateTimeImmutable $date,
        $roundingMode = Money::ROUND_HALF_UP
    ): Money {
        $baseCurrency = $money->getCurrency();
        $ratio = $this->historicalExchangeRate($baseCurrency, $counterCurrency, $date)->getValue();

        $baseCurrencySubunit = $this->currencies->subunitFor($baseCurrency);
        $counterCurrencySubunit = $this->currencies->subunitFor($counterCurrency);
        $subunitDifference = $baseCurrencySubunit - $counterCurrencySubunit;

        $ratio = (string) Number::fromFloat($ratio)->base10($subunitDifference);

        $counterValue = $money->multiply($ratio, $roundingMode);

        return new Money($counterValue->getAmount(), $counterCurrency);
    }

    private function historicalExchangeRate(
        Currency $baseCurrency,
        $counterCurrency,
        \DateTimeImmutable $date
    ): ExchangeRate {
        try {
            return $this->swap->historical($baseCurrency->getCode() . '/' . $counterCurrency->getCode(), $date);
        } catch (UnresolvableCurrencyPairException $exception) {
            try {
                $rate = $this->swap->historical($counterCurrency->getCode() . '/' . $baseCurrency->getCode(), $date);
                return new \Exchanger\ExchangeRate(
                    new CurrencyPair($counterCurrency->getCode(), $baseCurrency->getCode()),
                    (1 / $rate->getValue()),
                    $rate->getDate(),
                    $rate->getProviderName()
                );
            } catch (UnresolvableCurrencyPairException $inversedException) {
                throw $exception;
            }
        }
    }
}
