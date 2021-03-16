<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\TxFeeStrategy\StrategyInterface;

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
        $this->transactions[] = $transaction;
    }

    public function filterBySameWeek(\DateTimeImmutable $dateTime): self
    {
        $history = new self();
        $mondayTimestamp = strtotime('monday this week', $dateTime->getTimestamp());
        $nextMondayTimestamp = strtotime('monday next week', $dateTime->getTimestamp());
        foreach ($this->transactions as $tx) {
            $txTimestamp = $tx->getDate()->getTimestamp();
            if ($txTimestamp > $mondayTimestamp && $txTimestamp < $nextMondayTimestamp) {
                $history->add($tx);
            }
        }
        return $history;
    }

    public function filterAllBeforeTx(Transaction $curTx): self
    {
        $history = new self();
        foreach ($this->transactions as $tx) {
            if ($curTx === $tx) {
                return $history;
            }
            $history->add($tx);
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

    public function filterByFeeStrategySupport(StrategyInterface $feeStrategy): self
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
