<?php

declare(strict_types=1);

namespace App\Service\FileParser;

class CsvFileParser implements FileParserInterface
{
    public function parse(string $filename): array
    {
        return array_map('str_getcsv', file($filename));
    }
}
