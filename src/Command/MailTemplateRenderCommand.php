<?php

namespace App\Command;

use App\Repository\MailTemplateRepository;
use App\Service\MailTemplateHelper;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:mail-template:render',
    description: 'Render a mail template',
)]
class MailTemplateRenderCommand extends Command
{
    public function __construct(private MailTemplateRepository $mailTemplateRepository, private MailTemplateHelper $templateHelper, private EntityManagerInterface $entityManager, private Filesystem $filesystem)
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('template', InputArgument::REQUIRED, 'Template name or id')
            ->addOption('entity-type', null, InputOption::VALUE_REQUIRED, 'The entity type to use for data')
            ->addOption('entity-id', null, InputOption::VALUE_REQUIRED, 'The entity id to use for data')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Write rendered template to file in stead af stdout')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $templateIdentifier = $input->getArgument('template');
        // Find template by name or id.
        $mailTemplate = null;
        try {
            $mailTemplate = $this->mailTemplateRepository->findOneBy(['name' => $templateIdentifier])
                ?? $this->mailTemplateRepository->findOneBy(['id' => $templateIdentifier]);
        } catch (ConversionException $conversionException) {
            // $templateIdentifier may not be a valid uuid.
        }
        if (null === $mailTemplate) {
            throw new InvalidArgumentException(sprintf('Invalid template identifier: %s', var_export($templateIdentifier, true)));
        }
        $entity = null;
        $entityType = $input->getOption('entity-type');
        if (null !== $entityType) {
            $entityId = $input->getOption('entity-id');
            if (null === $entityId) {
                throw new RuntimeException('Missing option --entity-id');
            }

            try {
                $repo = $this->entityManager->getRepository($entityType);
                $entity = $repo->findOneBy(['id' => $entityId]);
            } catch (MappingException $exception) {
                throw new InvalidArgumentException(sprintf('Invalid entity type: %s', var_export($entityType, true)));
            } catch (ConversionException $conversionException) {
                throw new InvalidArgumentException(sprintf('Invalid entity id for %s: %s', var_export($entityType, true), var_export($entityId, true)));
            }

            if (null === $entity) {
                throw new InvalidArgumentException(sprintf('Cannot find entity of type %s with id %s', var_export($entityType, true), var_export($entityId, true)));
            }
        }

        $filename = $this->templateHelper->renderMailTemplate($mailTemplate, $entity);

        if ($output = $input->getOption('output')) {
            $this->filesystem->mkdir(dirname($output), 0755);
            $this->filesystem->rename($filename, $output, true);
            $io->success(sprintf('Rendered template written to file: %s', $output));
        } else {
            readfile($filename);
        }

        return Command::SUCCESS;
    }
}
