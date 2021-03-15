<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\TxFeeStrategy\StrategyInterface;
use Better\Nanoid\Client;

class TransactionsHistory implements \IteratorAggregate
{
    /**
     * @var Transaction[]
     */
    private $transactions = [];

    /**
     * @return Transaction[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->transactions);
    }

    public function add(Transaction $transaction)
    {
        $this->transactions[(new Client())->produce($size = 32)] = $transaction;
    }

    public function filterBySameWeek(\DateTimeImmutable $dateTime): self
    {
        $history = new self();
        $mondayTimestamp = strtotime('monday this week', $dateTime->getTimestamp());
        foreach ($this->transactions as $tx) {
            if ($tx->getDate()->getTimestamp() > $mondayTimestamp) {
                $history->add($tx);
            }
        }
        return $history;
    }

    public function filterUpToDate(\DateTimeImmutable $dateTime): self
    {
        $history = new self();
        foreach ($this->transactions as $tx) {
            if ($tx->getDate()->getTimestamp() < $dateTime->getTimestamp()) {
                $history->add($tx);
            }
        }
        return $history;
    }

    public function filterByClient(int $clientId): self
    {
        $history = new self();
        foreach ($this->transactions as $tx) {
            if ($tx->getClientId() === $clientId) {
                $history->add($tx);
            }
        }
        return $history;
    }

    public function filterByFeeStrategySupport(StrategyInterface $feeStrategy)
    {
        $history = new self();
        foreach ($this->transactions as $tx) {
            if ($feeStrategy->isSupported($tx)) {
                $history->add($tx);
            }
        }
        return $history;
    }
}
