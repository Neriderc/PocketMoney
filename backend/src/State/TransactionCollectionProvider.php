<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Child;
use App\Entity\User;
use App\Entity\Household;
use App\Repository\AccountRepository;
use App\Repository\ChildRepository;
use App\Repository\ScheduledTransactionRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class TransactionCollectionProvider implements ProviderInterface
{
    private ChildRepository $childRepository;
    private TransactionRepository $transactionRepository;
    private AccountRepository $accountRepository;

    public function __construct(
        private Security $security,
        ChildRepository $childRepository,
        TransactionRepository $transactionRepository,
        AccountRepository $accountRepository,
    ) {
        $this->childRepository = $childRepository;
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $account = $this->accountRepository->find($uriVariables['id']);
        $child = $account->getChild();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->transactionRepository->findBy(['account' => $account]);
        }

        if ($child->getHousehold()->getUsers()->contains($user) ||
            $child->getLinkedUser() === $user
        ) {
            return $this->transactionRepository->findBy(['account' => $account]);
        }

        throw new AccessDeniedHttpException();
    }
}