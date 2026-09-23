<?php

namespace App\Command;

use App\Service\AddressHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdresseVaskCommand extends Command
{
    protected static $defaultName = 'tvist1:adresse:vask';
    protected static $defaultDescription = 'Wash addresses using Adressevask';

    private AddressHelper $addressHelper;

    public function __construct(AddressHelper $addressHelper)
    {
        parent::__construct();
        $this->addressHelper = $addressHelper;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('addresses', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'One or more addresses')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addresses = $input->getArgument('addresses');

        $result = Command::SUCCESS;
        foreach ($addresses as $address) {
            try {
                $addressData = $this->addressHelper->fetchAddressData($address);
                $output->writeln([
                    $address,
                    json_encode($addressData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);
            } catch (\Exception $exception) {
                $output->writeln([
                    $address,
                    sprintf('<error>%s</error>', $exception->getMessage()),
                ]);
                $result = Command::FAILURE;
            }
        }

        return $result;
    }
}
