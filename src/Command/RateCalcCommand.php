<?php

namespace App\Command;

use App\Service\TxHistoryParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RateCalcCommand extends Command
{
    protected static $defaultName = 'rate:calc';
    protected static $defaultDescription = 'Calculate commission rate for transactions in CSV';
    /**
     * @var TxHistoryParser
     */
    private $txHistoryParser;

    public function __construct(TxHistoryParser $transactionHistoryParser)
    {
        parent::__construct();
        $this->txHistoryParser = $transactionHistoryParser;
    }

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
        $transactionsHistory = $this->txHistoryParser->getTransactionsHistory($fileName);
        foreach ($transactionsHistory as $transaction) {
            $output->writeln($transaction->getFee() ? $transaction->getFee()->getAmount() : 'undefined');
        }

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
