<?php

namespace App\Command;

use App\Service\TxFeeCalculator;
use App\Service\TxHistoryParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RateCalcCommand extends Command
{
    protected static $defaultName = 'rate:calc';
    protected static $defaultDescription = 'Calculate commission rate for transactions in CSV';
    private $txHistoryParser;
    private $txFeeCalculator;

    public function __construct(TxHistoryParser $txHistoryParser, TxFeeCalculator $txFeeCalculator)
    {
        parent::__construct();
        $this->txHistoryParser = $txHistoryParser;
        $this->txFeeCalculator = $txFeeCalculator;
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
        $transactionsHistory = $this->txFeeCalculator->calculateTxFees($transactionsHistory);
        foreach ($transactionsHistory as $transaction) {
            $output->writeln(
                $transaction->getFee()
                    ? ($transaction->getFee()->getAmount() ? sprintf('%.2F', $transaction->getFee()->getAmount()) : '0')
                    : 'undefined'
            );
        }

        return Command::SUCCESS;
    }
}
