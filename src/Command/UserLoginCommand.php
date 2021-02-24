<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserLoginCommand extends Command
{
    protected static $defaultName = 'app:user-login';
    protected static $defaultDescription = 'Add a short description for your command';
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('username', InputArgument::REQUIRED, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        if ($username) {
            $io->note(sprintf('You passed an argument: %s', $username));
        }
        // Lookup username in DB, fetch the token and then pass it on
        // Call some method or service to create the absolute url to be output via success
        $token = '';
        $loginPage = $this->urlGenerator->generate('default', [
            'user_login_token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $io->success('URL:   '.$loginPage);

        return Command::SUCCESS;
    }
}
