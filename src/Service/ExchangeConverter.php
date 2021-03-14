<?php

declare(strict_types=1);

namespace App\Service;

use Money\Currency;
use Money\Exception\UnresolvableCurrencyPairException;
use Money\Money;
use Swap\Swap;

final class ExchangeConverter
{
    /**
     * @var Swap
     */
    private Swap $swap;

    /**
     * @param Swap $swap
     */
    public function __construct(Swap $swap)
    {
        $this->swap = $swap;
    }

    public function convert(Money $money, Currency $currency, \DateTimeImmutable $date): Money
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
