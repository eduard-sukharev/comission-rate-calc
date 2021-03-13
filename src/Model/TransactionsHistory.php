<?php

declare(strict_types=1);

namespace App\Model;

use Better\Nanoid\Client;

class TransactionsHistory implements \IteratorAggregate
{
    /**
     * @var Transaction[]
     */
    private $transactions;

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

    public function getSameWeekTransactions(\DateTimeImmutable $dateTime): self
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

    public function getTransactionsUpToDate(\DateTimeImmutable $dateTime): self
    {
        $history = new self();
        foreach ($this->transactions as $tx) {
            if ($tx->getDate()->getTimestamp() < $dateTime->getTimestamp()) {
                $history->add($tx);
            }
        }
        return $history;
    }
}
