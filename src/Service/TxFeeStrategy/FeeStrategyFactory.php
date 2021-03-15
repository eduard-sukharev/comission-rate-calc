<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Service\HistoryConverter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

class FeeStrategyFactory
{
    private HistoryConverter $exchangeConverter;

    public function __construct(HistoryConverter $exchangeConverter)
    {
        $this->exchangeConverter = $exchangeConverter;
    }

    public function createStrategy(Transaction $tx)
    {
        if ($tx->isDeposit()) {
            return new DepositFeeStrategy();
        }
        if ($tx->isWithdraw()) {
            if ($tx->isClientBusiness()) {
                return new BusinessWithdrawFeeStrategy();
            }
            if ($tx->isClientPrivate()) {
                return new PrivateWithdrawFeeStrategy($this->exchangeConverter);
            }
        }

        // No defined rule found, assume no fee
        return new NoFeeStrategy();
    }
}
