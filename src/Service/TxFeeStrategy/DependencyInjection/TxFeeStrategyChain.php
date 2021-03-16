<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy\DependencyInjection;

use App\Service\TxFeeStrategy\StrategyInterface;

class TxFeeStrategyChain
{
    private array $strategies = [];

    public function addStrategy(StrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    /**
     * @return StrategyInterface[]
     */
    public function getStrategy(): ?\Generator
    {
        yield from $this->strategies;
    }
}
