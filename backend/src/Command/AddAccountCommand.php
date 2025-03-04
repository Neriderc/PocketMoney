<?php

namespace App\Command;

use App\Service\ChildService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-account',
    description: 'Add a new account to a child',
)]
class AddAccountCommand extends Command
{
    private ChildService $childService;
    public function __construct(ChildService $childService)
    {
        parent::__construct();
        $this->childService = $childService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('childId', InputArgument::REQUIRED, 'The ID of the child to add the account to')
            ->addArgument('accountName', InputArgument::REQUIRED, 'The name to use for the new account')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $childId = $input->getArgument('childId');
        $accountName = $input->getArgument('accountName');

        try {
            $data = [
                'name' => $accountName,
                'color' => '#FFFFFF',
                'icon' => '',
            ];
            $child = $this->childService->addAccountToChild($childId, $data);
            $io->success(sprintf('Account "%s" added to child ID %d', $accountName, $childId));
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}