<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Transaction;
use App\Model\TransactionsHistory;
use App\Service\FileParser\FileParserInterface;
use Money\Currency;
use Money\MoneyParser;

class TxHistoryParser
{
    private const ROW_OFFSET_DATE = 0;
    private const ROW_OFFSET_CLIENT_ID = 1;
    private const ROW_OFFSET_CLIENT_TYPE = 2;
    private const ROW_OFFSET_TRANSACTION_TYPE = 3;
    private const ROW_OFFSET_AMOUNT = 4;
    private const ROW_OFFSET_CURRENCY = 5;

    private MoneyParser $moneyParser;

    public function __construct(
        FileParserInterface $fileParser,
        MoneyParser $moneyParser
    ) {
        $this->fileParser = $fileParser;
        $this->moneyParser = $moneyParser;
    }

    public function getTransactionsHistory(string $transactionsfilename)
    {
        $history = new TransactionsHistory();
        foreach ($this->fileParser->getLines($transactionsfilename) as $row) {
            $history->add(
                (new Transaction())
                    ->setDate(new \DateTimeImmutable($row[self::ROW_OFFSET_DATE]))
                    ->setClientId((int) $row[self::ROW_OFFSET_CLIENT_ID])
                    ->setClientType($row[self::ROW_OFFSET_CLIENT_TYPE])
                    ->setType($row[self::ROW_OFFSET_TRANSACTION_TYPE])
                    ->setValue(
                        $this->moneyParser->parse(
                            $row[self::ROW_OFFSET_AMOUNT],
                            new Currency($row[self::ROW_OFFSET_CURRENCY])
                        )
                    )
            );
        }

        return $history;
    }
}
