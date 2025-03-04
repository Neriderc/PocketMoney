<?php

namespace App\Service;

use App\Entity\Child;
use App\Entity\Household;
use App\Enum\AmountBase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChildRepository;

class ChildService
{
    private EntityManagerInterface $entityManager;
    private ChildRepository $childRepository;
    private AccountService $accountService;
    private TransactionScheduleService $transactionScheduleService;

    public function __construct(EntityManagerInterface $entityManager, ChildRepository $childRepository, AccountService $accountService, TransactionScheduleService $transactionScheduleService)
    {
        $this->entityManager = $entityManager;
        $this->childRepository = $childRepository;
        $this->accountService = $accountService;
        $this->transactionScheduleService = $transactionScheduleService;
    }

    public function createChild(Household $household, string $name): Child
    {
        $child = new Child();
        $child->setName($name);
        $child->setHousehold($household);

        $this->entityManager->persist($child);
        $this->entityManager->flush();

        return $child;
    }

    public function addAccountToChild(int $childId, array $data): Child
    {
        $child = $this->childRepository->find($childId);
        if (!$child) {
            throw new \Exception('Child not found');
        }

        $account = $this->accountService->createAccount($child, $data);
        $child->addAccount($account);

        $this->entityManager->flush();

        return $child;
    }

    public function addTransactionScheduleToChild($childId, mixed $data)
    {
        $child = $this->childRepository->find($childId);
        if (!$child) {
            throw new \Exception('Child not found');
        }

        $transactionSchedule = $this->transactionScheduleService->createTransactionSchedule($child, $data);
        $child->addTransactionSchedule($transactionSchedule);

        $this->entityManager->flush();

        return $child;
    }
}
