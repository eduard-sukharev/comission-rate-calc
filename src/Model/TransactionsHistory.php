<?php
declare(strict_types=1);

namespace App\Model;

class TransactionsHistory implements \IteratorAggregate
{
    /**
     * @var Transaction
     */
    private $transactions;

    public function getIterator()
    {
        return new \ArrayIterator($this->transactions);
    }
}
