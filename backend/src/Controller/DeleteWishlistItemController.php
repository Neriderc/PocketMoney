<?php
namespace App\Controller;

use App\Entity\Wishlist;
use App\Entity\WishlistItem;
use App\Service\WishlistItemService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DeleteWishlistItemController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(
        #[MapEntity(id: 'wishlistId')] Wishlist         $wishlist,
        #[MapEntity(id: 'wishlistItemId')] WishlistItem $wishlistItem,
        WishlistItemService                             $wishlistItemService,
    ): JsonResponse
    {
        if (!$wishlist->getWishlistItems()->contains($wishlistItem)) {
            return new JsonResponse([
                'error' => 'Wishlist item not found for this wishlist.'
            ], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return new JsonResponse(
                ['error' => 'Authentication required.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $householdUsers = $wishlist->getChild()->getHousehold()->getUsers();
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$householdUsers->contains($user)
        ) {
            return new JsonResponse(
                ['error' => 'You do not have permission to delete this wishlist item.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $wishlist->removeWishlistItem($wishlistItem);
        $this->entityManager->remove($wishlistItem);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Wishlist item deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

}
