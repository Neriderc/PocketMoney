<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class TransactionCollectionProvider implements ProviderInterface
{
    private TransactionRepository $transactionRepository;
    private AccountRepository $accountRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        private Security $security,
        TransactionRepository $transactionRepository,
        AccountRepository $accountRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $account = $this->accountRepository->find($uriVariables['id']);

        if (!$account) {
            throw new NotFoundHttpException('Account not found.');
        }

        if ($this->security->isGranted('ROLE_ADMIN') ||
            $this->isUserAuthorized($user, $account)) {

            $page = $context['filters']['page'] ?? 1;
            $itemsPerPage = $context['filters']['itemsPerPage'] ?? 10;
            $offset = ($page - 1) * $itemsPerPage;

            $queryBuilder = $this->transactionRepository->createQueryBuilder('t')
                ->where('t.account = :account')
                ->setParameter('account', $account)
                ->orderBy('t.transactionDate', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($itemsPerPage);

            $query = $queryBuilder->getQuery();

            $paginator = new Paginator($query);

            return $paginator;
        }

        throw new AccessDeniedHttpException();
    }

    private function isUserAuthorized($user, Account $account): bool
    {
        $child = $account->getChild();
        return $child->getHousehold()->getUsers()->contains($user) ||
            $child->getLinkedUser() === $user;
    }
}