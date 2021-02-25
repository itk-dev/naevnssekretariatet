<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserLoginCommand extends Command
{
    protected static $defaultName = 'app:user-login';
    protected static $defaultDescription = 'Get login url for user';
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('email', InputArgument::REQUIRED, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if ($email) {
            $io->note(sprintf('You passed an argument: %s', $email));
        }
        // Lookup username in DB, fetch the token and then pass it on
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new RuntimeException('User not found in database');
        }
        // generate new token and set it on the user
        // persist
        // flush
        $token = $user->getLoginToken();
        $loginPage = $this->urlGenerator->generate('default', [
            'loginToken' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $io->success('URL:   '.$loginPage);

        return Command::SUCCESS;
    }
}
