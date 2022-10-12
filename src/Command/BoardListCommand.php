<?php

namespace App\Command;

use App\Repository\MunicipalityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tvist1:board:list',
    description: 'Lists useful information about all boards available',
)]
class BoardListCommand extends Command
{
    public function __construct(private MunicipalityRepository $municipalityRepository)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $municipalities = $this->municipalityRepository->findAll();

        // @Example result
        // Boards:
        // Aarhus Hegnsnævnet FenceReviewCaseType: 0d95ca9f-b8bc-48b4-919a-a01920b1814d
        // Aarhus Beboerklagenævnet ResidentComplaintBoardCaseType: 42c4ae80-24f9-4845-812b-14ffcdb3cced
        // Aarhus Huslejenævnet RentBoardCaseType: 6fab9715-bba8-421f-b8fe-bbf7e4a67c98
        // Randers Huslejenævnet RentBoardCaseType: 3071165a-6fba-401b-8026-7b77ebe12ae3
        // Randers Beboerklagenævnet ResidentComplaintBoardCaseType: 8ad30f8f-fcad-4c28-8ef4-027b7889085f
        // Randers Hegnsnævnet FenceReviewCaseType: 9026ec57-4080-4687-82ec-8b979b57412e
        echo 'Boards:'.PHP_EOL;
        foreach ($municipalities as $municipality) {
            foreach ($municipality->getBoards() as $board) {
                echo $municipality->getName().' '.$board->getName().' '.$board->getCaseFormType().': '.$board->getId()->__toString().PHP_EOL;
            }
        }

        return Command::SUCCESS;
    }
}
