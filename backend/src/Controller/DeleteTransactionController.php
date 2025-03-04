<?php
namespace App\Controller;

use App\Entity\Child;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Service\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DeleteTransactionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(
        #[MapEntity(id: 'accountId')] Account $account,
        #[MapEntity(id: 'transactionId')] Transaction $transaction,
        AccountService $accountService
    ): JsonResponse
    {
        if (!$account->getTransactions()->contains($transaction)) {
            return new JsonResponse([
                'error' => 'Transaction not found for this account.'
            ], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return new JsonResponse(
                ['error' => 'Authentication required.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $householdUsers = $account->getChild()->getHousehold()->getUsers();
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$householdUsers->contains($user)
        ) {
            return new JsonResponse(
                ['error' => 'You do not have permission to delete this transaction.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $account->removeTransaction($transaction);
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
        $accountService->updateBalance($account);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Transaction deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

}
