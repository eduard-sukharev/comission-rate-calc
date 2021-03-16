<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy;

use App\Model\Transaction;
use App\Service\HistoryConverter;

class FeeStrategyFactory
{
    private HistoryConverter $exchangeConverter;

    /**
     * @var StrategyInterface[]
     */
    private array $strategies;

    public function __construct(HistoryConverter $exchangeConverter)
    {
        $this->exchangeConverter = $exchangeConverter;

        $this->strategies = [
            new DepositFeeStrategy(),
            new BusinessWithdrawFeeStrategy(),
            new PrivateWithdrawFeeStrategy($this->exchangeConverter),
            new NoFeeStrategy(),
        ];
    }

    public function createStrategy(Transaction $tx): ?StrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->isSupported($tx)) {
                return $strategy;
            };
        }

        return null;
    }
}
