<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Child;
use App\Entity\Transaction;
use App\Entity\ScheduledTransaction;
use App\Enum\AmountBase;
use App\Enum\RepeatFrequency;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use InvalidArgumentException;

class TransactionService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTransaction(Account $account, array $data): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($data["amount"]);
        $transaction->setDescription($data["description"]);
        $transaction->setComment($data["comment"]);
        $transactionDate = DateTimeImmutable::createFromFormat('Y-m-d', $data["transactionDate"]);
        if (!$transactionDate) {
            throw new InvalidArgumentException("Invalid date format for nextExecutionDate.");
        }
        $transaction->setTransactionDate($transactionDate);
        $transaction->setAccount($account);
        $account->addTransaction($transaction);

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $transaction;
    }
}
