<?php

namespace App\Command;

use App\Service\TxFeeCalculator;
use App\Service\TxHistoryParser;
use Money\MoneyFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RateCalcCommand extends Command
{
    protected static $defaultName = 'rate:calc';
    protected static $defaultDescription = 'Calculate commission rate for transactions in CSV';
    private TxHistoryParser $txHistoryParser;
    private TxFeeCalculator $txFeeCalculator;
    private MoneyFormatter $moneyFormatter;

    public function __construct(
        TxHistoryParser $txHistoryParser,
        TxFeeCalculator $txFeeCalculator,
        MoneyFormatter $moneyFormatter
    ) {
        parent::__construct();
        $this->txHistoryParser = $txHistoryParser;
        $this->txFeeCalculator = $txFeeCalculator;
        $this->moneyFormatter = $moneyFormatter;
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
        echo PHP_EOL;
        $fileName = $input->getArgument('transactions_file');
        $transactionsHistory = $this->txHistoryParser->getTransactionsHistory($fileName);
        $transactionsHistory = $this->txFeeCalculator->calculateTxFees($transactionsHistory);
        foreach ($transactionsHistory as $transaction) {
            if ($transaction->getFee()) {
                $output->writeln($this->moneyFormatter->format($transaction->getFee()));
            } else {
                $output->writeln('undefined');
            }
        }

        return Command::SUCCESS;
    }
}
