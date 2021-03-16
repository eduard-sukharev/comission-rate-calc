<?php

declare(strict_types=1);

namespace App\Service\FileParser;

class FileParser implements FileParserInterface
{
    public function getLines(string $filename): \Generator
    {
        try {
            $handle = fopen($filename, 'r');

            while (($line = fgets($handle)) !== false) {
                yield $line;
            }
        } finally {
            if ($handle) {
                fclose($handle);
            }
        }
    }
}
