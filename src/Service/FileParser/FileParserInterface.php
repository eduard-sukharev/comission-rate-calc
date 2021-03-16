<?php

declare(strict_types=1);

namespace App\Service\FileParser;

interface FileParserInterface
{
    /**
     * @param $filename
     * @return \Generator Content rows generator
     */
    public function getLines(string $filename): \Generator;
}
