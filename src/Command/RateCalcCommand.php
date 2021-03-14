<?php

namespace App\Command;

use App\Service\TxFeeCalculator;
use App\Service\TxHistoryParser;
use Money\Currencies\ISOCurrencies;
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
        echo PHP_EOL;
        $fileName = $input->getArgument('transactions_file');
        $transactionsHistory = $this->txHistoryParser->getTransactionsHistory($fileName);
        $transactionsHistory = $this->txFeeCalculator->calculateTxFees($transactionsHistory);
        $isoCurrencies = new ISOCurrencies();
        foreach ($transactionsHistory as $transaction) {
            if ($transaction->getFee()) {
                $feeScale = $isoCurrencies->subunitFor($transaction->getFee()->getCurrency());
                $fee = $transaction->getFee()->getAmount() / (10 ** $feeScale);
                $outputFormat = '%.' . $feeScale . 'F';
//                var_dump($fee);
//                var_dump($outputFormat);
                $output->writeln(sprintf($outputFormat, $fee));
            } else {
                $output->writeln('undefined');
            }
        }

        return Command::SUCCESS;
    }
}
