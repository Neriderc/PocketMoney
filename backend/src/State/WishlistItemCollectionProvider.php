<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Wishlist;
use App\Repository\WishlistItemRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class WishlistItemCollectionProvider implements ProviderInterface
{
    private WishlistItemRepository $wishlistItemRepository;
    private WishlistRepository $wishlistRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        private Security       $security,
        WishlistItemRepository $wishlistItemRepository,
        WishlistRepository     $wishlistRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->wishlistItemRepository = $wishlistItemRepository;
        $this->wishlistRepository = $wishlistRepository;
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $wishlist = $this->wishlistRepository->find($uriVariables['id']);

        if (!$wishlist) {
            throw new NotFoundHttpException('Wishlist not found.');
        }

        if ($this->security->isGranted('ROLE_ADMIN') ||
            $this->isUserAuthorized($user, $wishlist)) {

            $page = $context['filters']['page'] ?? 1;
            $itemsPerPage = $context['filters']['itemsPerPage'] ?? 10;
            $offset = ($page - 1) * $itemsPerPage;

            $queryBuilder = $this->wishlistItemRepository->createQueryBuilder('t')
                ->where('t.wishlist = :account')
                ->setParameter('account', $wishlist)
                ->orderBy('t.priority', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($itemsPerPage);

            $query = $queryBuilder->getQuery();

            $paginator = new Paginator($query);

            return $paginator;
        }

        throw new AccessDeniedHttpException();
    }

    private function isUserAuthorized($user, Wishlist $wishlist): bool
    {
        $child = $wishlist->getChild();
        return $child->getHousehold()->getUsers()->contains($user) ||
            $child->getLinkedUser() === $user;
    }
}