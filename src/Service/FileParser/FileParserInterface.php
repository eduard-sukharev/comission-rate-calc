<?php

declare(strict_types=1);

namespace App\Service\FileParser;

interface FileParserInterface
{
    /**
     * @param $filename
     * @return array[] Content rows, each represented as array of parsed values
     */
    public function parse(string $filename): array;
}
