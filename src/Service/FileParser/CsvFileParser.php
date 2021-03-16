<?php

declare(strict_types=1);

namespace App\Service\FileParser;

class CsvFileParser implements FileParserInterface
{
    public function parse(string $filename): array
    {
        $csvLines = [];
        foreach ($this->getLines($filename) as $line) {
            $csvLines[] = str_getcsv($line);
        }

        return array_filter($csvLines, fn ($line) => count($line) > 1);
    }

    private function getLines(string $filename): array
    {
        return file($filename);
    }
}
