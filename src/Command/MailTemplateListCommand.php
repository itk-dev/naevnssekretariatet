<?php

namespace App\Command;

use App\Repository\MailTemplateRepository;
use App\Service\MailTemplateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'tvist1:mail-template:list',
    description: 'List mail templates',
)]
class MailTemplateListCommand extends Command
{
    public function __construct(private readonly MailTemplateRepository $mailTemplateRepository, private readonly MailTemplateHelper $templateHelper, private readonly EntityManagerInterface $entityManager, private readonly Filesystem $filesystem)
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addOption('entity-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The template entity type')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $criteria = [];
        $templates = $this->mailTemplateRepository->findBy($criteria);

        foreach ($templates as $template) {
            $io->definitionList(
                ['Id' => $template->getId()],
                ['Name' => $template->getName()],
                ['Type' => $template->getType()],
                ['Entity types' => implode(PHP_EOL, $this->templateHelper->getTemplateEntityClassNames($template) ?? [])],
                ['Description' => $template->getDescription()],
                ['File name' => $template->getTemplateFilename()],
                ['Created at' => $template->getCreatedAt()->format(\DateTimeInterface::ATOM)],
                ['Updated at' => $template->getUpdatedAt()->format(\DateTimeInterface::ATOM)],
            );
        }

        return Command::SUCCESS;
    }
}
