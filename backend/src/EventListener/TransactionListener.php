<?php
namespace App\EventListener;

use App\Entity\Transaction;
use App\Service\AccountService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Psr\Log\LoggerInterface;

class TransactionListener
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly AccountService             $accountService,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $this->handleTransactionChange($event);
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $this->handleTransactionChange($event);
    }

    public function postRemove(PostRemoveEventArgs $event): void
    {
    }

    private function handleTransactionChange(PostPersistEventArgs|PostUpdateEventArgs|PostRemoveEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Transaction) {
            return;
        }

        $account = $entity->getAccount();
        if (!$account) {
            return;
        }
        $this->accountService->updateBalance($account);
        $entityManager = $event->getObjectManager();
        $entityManager->persist($account);
        $entityManager->flush();
    }
}

