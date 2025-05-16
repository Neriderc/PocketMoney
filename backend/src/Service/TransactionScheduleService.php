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

class TransactionScheduleService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTransactionSchedule(Child $child, array $data): ScheduledTransaction
    {
        $transactionSchedule = new ScheduledTransaction();
        $transactionSchedule->setAmount($data["amount"]);
        $transactionSchedule->setDescription($data["description"]);
        $transactionSchedule->setComment($data["comment"]);
        try {
            $repeatFrequency = RepeatFrequency::from($data['repeatFrequency']);
        } catch (Error) {
            throw new InvalidArgumentException("Invalid repeat frequency");
        }
        $transactionSchedule->setRepeatFrequency($repeatFrequency);
        $nextExecutionDate = DateTimeImmutable::createFromFormat('Y-m-d', $data["nextExecutionDate"]);
        if (!$nextExecutionDate) {
            throw new InvalidArgumentException("Invalid date format for nextExecutionDate.");
        }
        $transactionSchedule->setNextExecutionDate($nextExecutionDate);

        $transactionSchedule->setChild($child);

        try {
            $amountBase = AmountBase::from($data['amountCalculation']);
        } catch (Error) {
            throw new InvalidArgumentException("Invalid amount calculation value");
        }
        $transactionSchedule->setAmountBase($amountBase);

        if (!empty($data['accounts']) && is_array($data['accounts'])) {
            foreach ($data['accounts'] as $accountIri) {
                $matches = [];
                if (preg_match('#/accounts/(\d+)$#', $accountIri, $matches)) {
                    $accountId = (int)$matches[1];
                    $account = $this->entityManager->getRepository(Account::class)->find($accountId);
                    if (!$account) {
                        throw new InvalidArgumentException("Account not found for ID: $accountId");
                    }
                    $transactionSchedule->addAccount($account);
                } else {
                    throw new InvalidArgumentException("Invalid account IRI format: $accountIri");
                }
            }
        }

        $this->entityManager->persist($transactionSchedule);
        $this->entityManager->flush();

        return $transactionSchedule;
    }

    public function processTransactionsForChild(Child $child): void
    {
        $transactionSchedules = $this->entityManager->getRepository(ScheduledTransaction::class)
            ->findBy(['child' => $child]);

        foreach ($transactionSchedules as $transactionSchedule) {
            $this->createTransactionsIfNeeded($transactionSchedule);
        }
    }

    private function createTransactionsIfNeeded(ScheduledTransaction $transactionSchedule): void
    {
        $now = new \DateTimeImmutable();

        while ($transactionSchedule->getNextExecutionDate() <= $now) {
            $accounts = $transactionSchedule->getAccounts();
            $AmountBase = $transactionSchedule->getAmountBase();
            $numAccounts = $accounts->count();

            if ($numAccounts === 0) {
                return;
            }

            $totalAmount = $transactionSchedule->getAmount();

            // If based on age, the amount is multiplied by the child's age
            if ($AmountBase == AmountBase::AGE) {
                $child = $transactionSchedule->getChild();
                $age = $child->getDateOfBirth()->diff($transactionSchedule->getNextExecutionDate())->y;
                $totalAmount *= $age;
            }
            $splitAmount = $totalAmount / $numAccounts;

            foreach ($accounts as $account) {
                $transaction = new Transaction();
                $transaction->setTransactionDate($transactionSchedule->getNextExecutionDate());
                $transaction->setComment($transactionSchedule->getComment());
                $transaction->setAccount($account);
                $transaction->setAmount($splitAmount);
                $transaction->setDescription($transactionSchedule->getDescription());

                $this->entityManager->persist($transaction);
            }
            $this->updateNextExecutionDate($transactionSchedule);
        }

        $this->entityManager->flush();
    }


    private function updateNextExecutionDate(ScheduledTransaction $transactionSchedule): void
    {
        $nextExecutionDate = $transactionSchedule->getNextExecutionDate();
        switch ($transactionSchedule->getRepeatFrequency()) {
            case RepeatFrequency::DAILY:
                $nextExecutionDate = $nextExecutionDate->modify('+1 day');
                break;
            case RepeatFrequency::WEEKLY:
                $nextExecutionDate = $nextExecutionDate->modify('+1 week');
                break;
            case RepeatFrequency::MONTHLY:
                $nextExecutionDate = $nextExecutionDate->modify('+1 month');
                break;

        }

        $transactionSchedule->setNextExecutionDate($nextExecutionDate);
        $this->entityManager->persist($transactionSchedule);
    }
}
