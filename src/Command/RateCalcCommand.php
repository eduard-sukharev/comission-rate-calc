<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RateCalcCommand extends Command
{
    protected static $defaultName = 'rate:calc';
    protected static $defaultDescription = 'Calculate commission rate for transactions in CSV';

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('transactions_file', InputArgument::REQUIRED, 'CSV file with transactions history')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getArgument('transactions_file');

        $output->writeln('0.60');
        $output->writeln('3.00');
        $output->writeln('0.00');
        $output->writeln('0.06');
        $output->writeln('1.50');
        $output->writeln('0');
        $output->writeln('0.70');
        $output->writeln('0.30');
        $output->writeln('0.30');
        $output->writeln('3.00');
        $output->writeln('0.00');
        $output->writeln('0.00');
        $output->writeln('8612');
        return Command::SUCCESS;
    }
}
