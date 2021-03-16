<?php

declare(strict_types=1);

namespace App\Service\FileParser;

class CsvFileParser implements FileParserInterface
{
    /**
     * @var FileParser
     */
    private FileParser $fileParser;

    public function __construct(FileParser $fileParser)
    {
        $this->fileParser = $fileParser;
    }

    public function getLines(string $filename): \Generator
    {
        foreach ($this->fileParser->getLines($filename) as $line) {
            $csvLine = str_getcsv($line);
            if (count($csvLine) > 1) {
                yield $csvLine;
            }
        }
    }
}
