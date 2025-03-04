<?php

namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user account',
)]
class CreateUserCommand extends Command
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username for the account')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password (will prompt if not provided)')
            ->addArgument('roles', InputArgument::OPTIONAL, 'Comma-separated list of roles (default: ROLE_USER)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $roles = $input->getArgument('roles') ? explode(',', $input->getArgument('roles')) : ['ROLE_USER'];

        // Prompt for password if not provided
        if (!$password) {
            $helper = $this->getHelper('question');
            if (!$helper instanceof QuestionHelper) {
                throw new \RuntimeException('The "question" helper is not available.');
            }
            $question = new Question('Enter password: ');
            $question->setHidden(true)->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }

        // Create user
        if ($this->userService->createUser($username, $password, $roles)) {
            $output->writeln("<info>User created: $username</info>");
        } else {
            $output->writeln("<error>User already exists or invalid data</error>");
        }

        return Command::SUCCESS;
    }
}
