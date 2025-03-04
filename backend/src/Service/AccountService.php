<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Child;
use App\Entity\Transaction;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChildRepository;

class AccountService
{
    private EntityManagerInterface $entityManager;
    private AccountRepository $accountRepository;
    private TransactionService $transactionService;

    public function __construct(EntityManagerInterface $entityManager, AccountRepository $accountRepository, TransactionService $transactionService)
    {
        $this->entityManager = $entityManager;
        $this->accountRepository = $accountRepository;
        $this->transactionService = $transactionService;
    }

    public function createAccount(Child $child, array $data): Account
    {
        $account = new Account();
        $account->setName($data["name"]);
        $account->setColor($data["color"]);
        $account->setIcon($data["icon"]);
        $account->setChild($child);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $account;
    }

    public function updateBalance(Account $account): void
    {
        $transactions = $account->getTransactions();
        $balance = 0;

        foreach ($transactions as $transaction) {
            $balance += $transaction->getAmount();
        }

        $account->setBalance($balance);
        $account->setUpdatedAt(new \DateTimeImmutable());
    }

    public function addTransactionToAccount($accountId, $data): Transaction
    {
        $account = $this->accountRepository->find($accountId);
        return $this->transactionService->createTransactionSchedule($account, $data);
    }
}
