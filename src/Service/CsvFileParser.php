<?php
declare(strict_types=1);

namespace App\Service;

class CsvFileParser
{
    public function parse($filename): TransactionsHistory
    {
        $csv = array_map('str_getcsv', file($filename));
    }
}
