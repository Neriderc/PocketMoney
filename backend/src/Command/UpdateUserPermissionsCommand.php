<?php

namespace App\Command;

use App\Exception\InvalidRoleException;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:UpdateUserPermissions',
    description: 'Update the roles for a specific user.',
)]
class UpdateUserPermissionsCommand extends Command
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
            ->addArgument('username', InputArgument::REQUIRED, 'Username of the account to update')
            ->addArgument('roles', InputArgument::REQUIRED, 'Comma seperated list of roles to assign to the user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $roles = explode(',', $input->getArgument('roles'));

        try {
            if ($this->userService->updateUserRoles($username, $roles)) {
                $io->success("User roles updated for $username.\nNew roles: " . implode(', ', $roles));
            }
        } catch (InvalidRoleException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
