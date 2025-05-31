<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Child;
use App\Entity\User;
use App\Entity\Household;
use App\Entity\Wishlist;
use App\Repository\ChildRepository;
use App\Repository\ScheduledTransactionRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class WishlistCollectionProvider implements ProviderInterface
{
    private ChildRepository $childRepository;
    private WishlistRepository $wishlistRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        private Security               $security,
        ChildRepository                $childRepository,
        WishlistRepository             $wishlistRepository,
        EntityManagerInterface         $entityManager,
    ) {
        $this->childRepository = $childRepository;
        $this->wishlistRepository = $wishlistRepository;
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $child = $this->childRepository->find($uriVariables['childId']);

        if (!$this->security->isGranted('ROLE_ADMIN')
            && !$child->getHousehold()->getUsers()->contains($user)
            && $child->getLinkedUser() !== $user
        ) {
            throw new AccessDeniedHttpException();
        }

        $wishlists = $this->wishlistRepository->findBy(['child' => $child]);
        if (empty($wishlists)) {
            $wishlist = new Wishlist();
            $wishlist->setChild($child);
            $this->entityManager->persist($wishlist);
            $this->entityManager->flush();

            $wishlists[] = $wishlist;
        }

        return $wishlists;
    }
}