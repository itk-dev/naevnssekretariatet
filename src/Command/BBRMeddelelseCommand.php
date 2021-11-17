<?php

namespace App\Command;

use App\Service\BBRHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BBRMeddelelseCommand extends Command
{
    protected static $defaultName = 'tvist1:bbr:meddelelse-url';
    protected static $defaultDescription = 'Get url to “BBR-Meddelelse';

    private BBRHelper $bbrHelper;

    public function __construct(BBRHelper $bbrHelper)
    {
        parent::__construct();
        $this->bbrHelper = $bbrHelper;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('addresses', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'One or more addresses')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format', 'pdf')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addresses = $input->getArgument('addresses');
        $format = $input->getOption('format');

        $table = new Table($output);
        $table->setHeaders(['Address', 'Url']);
        foreach ($addresses as $address) {
            $row = [$address];
            try {
                $row[] = $this->bbrHelper->getBBRMeddelelseUrl($address, $format);
            } catch (\Exception $exception) {
                $row[] = new TableCell($exception->getMessage(), [
                    'style' => new TableCellStyle([
                        'cellFormat' => '<error>%s</error>',
                    ]),
                ]);
            }
            $table->addRow($row);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
