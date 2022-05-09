<?php

namespace App\Command;

use App\Repository\MailTemplateRepository;
use App\Service\MailTemplateHelper;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'tvist1:mail-template:render',
    description: 'Render a mail template',
)]
class MailTemplateRenderCommand extends Command
{
    public function __construct(private MailTemplateRepository $mailTemplateRepository, private MailTemplateHelper $templateHelper, private EntityManagerInterface $entityManager, private Filesystem $filesystem, private ParameterBagInterface $parameters)
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('template', InputArgument::REQUIRED, 'Template name or id')
            ->addOption('entity-type', null, InputOption::VALUE_REQUIRED, 'The entity type to use for data')
            ->addOption('entity-id', null, InputOption::VALUE_REQUIRED, 'The entity id to use for data')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Write rendered template to file instead of stdout')
            ->addOption('dump-data', null, InputOption::VALUE_NONE, 'Dump template data (JSON) to stdout')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (docx or pdf)')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'The locale to use', $this->getDefaultLocale())
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locale = $input->getOption('locale');
        $this->setLocale($locale);

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

        $entityType = $input->getOption('entity-type');
        $entityId = $input->getOption('entity-id');
        $entity = $this->templateHelper->getPreviewEntity($mailTemplate, $entityType, $entityId);

        $dumpData = $input->getOption('dump-data');
        $outputName = $input->getOption('output');

        if ($dumpData) {
            $data = $this->templateHelper->getTemplateData($mailTemplate, $entity);
            $output->writeln(json_encode($data));
            // If we don't have an output file name to write the pdf to, we stop
            // now.
            if (!$outputName) {
                return Command::SUCCESS;
            }
        }

        $options = [];
        if ($format = $input->getOption('format')) {
            $options['format'] = $format;
        }
        $filename = $this->templateHelper->renderMailTemplate($mailTemplate, $entity, $options);

        if (null !== $outputName) {
            $this->filesystem->mkdir(dirname($outputName), 0755);
            $this->filesystem->rename($filename, $outputName, true);
            $io->success(sprintf('Rendered template written to file: %s', $outputName));
        } else {
            readfile($filename);
        }

        return Command::SUCCESS;
    }

    private function getDefaultLocale(): ?string
    {
        return $this->parameters->has('locale') ? $this->parameters->get('locale') : null;
    }

    private function setLocale(string $locale = null)
    {
        // @see \Symfony\Component\HttpFoundation\Request::setPhpDefaultLocale().
        try {
            if (class_exists(\Locale::class, false)) {
                \Locale::setDefault($locale ?? $this->getDefaultLocale());
            }
        } catch (\Exception $e) {
        }
    }
}
