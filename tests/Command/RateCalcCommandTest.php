<?php

declare(strict_types=1);

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RateCalcCommandTest extends KernelTestCase
{
    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    private $command;

    /**
     * @var resource
     */
    private $file;

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $this->file = tmpfile();
        $this->command = $application->find('rate:calc');
    }

    protected function tearDown(): void
    {
        fclose($this->file);
    }

    public function testExecute()
    {
        $testData = [
            ['2014-12-31', 4, 'private', 'withdraw', 1200.00, 'EUR'],
            ['2015-01-01', 4, 'private', 'withdraw', 1000.00, 'EUR'],
            ['2016-01-05', 4, 'private', 'withdraw', 1000.00, 'EUR'],
            ['2016-01-05', 1, 'private', 'deposit', 200.00, 'EUR'],
            ['2016-01-06', 2, 'business', 'withdraw', 300.00, 'EUR'],
            ['2016-01-06', 1, 'private', 'withdraw', 30000, 'JPY'],
            ['2016-01-07', 1, 'private', 'withdraw', 1000.00, 'EUR'],
            ['2016-01-07', 1, 'private', 'withdraw', 100.00, 'USD'],
            ['2016-01-10', 1, 'private', 'withdraw', 100.00, 'EUR'],
            ['2016-01-10', 2, 'business', 'deposit', 10000.00, 'EUR'],
            ['2016-01-10', 3, 'private', 'withdraw', 1000.00, 'EUR'],
            ['2016-02-15', 1, 'private', 'withdraw', 300.00, 'EUR'],
            ['2016-02-19', 5, 'private', 'withdraw', 3000000, 'JPY'],
        ];
        foreach ($testData as $row) {
            fputcsv($this->file, $row);
        }
        $filename = stream_get_meta_data($this->file)['uri'];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['transactions_file' => $filename]);

        $output = $commandTester->getDisplay();
        $expectedLines = [
            '0.60',
            '3.00',
            '0.00',
            '0.06',
            '1.50',
            '0',
            '0.70',
            '0.30',
            '0.30',
            '3.00',
            '0.00',
            '0.00',
            '8612',
        ];
        $actualLines = array_filter($this->splitByLines($output), fn($line) => $line !== '');
        var_dump($actualLines);
        self::assertCount(count($expectedLines), $actualLines);
        foreach ($actualLines as $i => $actualLine) {
            echo $actualLine . PHP_EOL;
            self::assertEquals($expectedLines[$i], $actualLine);
        }
    }

    /**
     * @param string $output
     * @return string[]
     */
    protected function splitByLines(string $output)
    {
        return explode("\n", $output);
    }
}
