<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Wishlist;
use App\Service\WishlistItemService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddWishlistItemController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private WishlistItemService $wishlistItemService;

    public function __construct(EntityManagerInterface $entityManager, Security $security, WishlistItemService $wishlistItemService)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->wishlistItemService = $wishlistItemService;
    }

    public function __invoke(Request $request, $wishlistId): JsonResponse
    {
        $wishlist = $this->entityManager->getRepository(Wishlist::class)->find($wishlistId);

        $user = $this->security->getUser();

        if (!$user instanceof User || !$wishlist) {
            throw new BadRequestHttpException('Failed to get entities.');
        }

        if (
            !in_array('ROLE_ADMIN', $user->getRoles(), true) &&
            !$wishlist->getChild()->getHousehold()->getUsers()->contains($user)
        ) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['description'])) {
            throw new BadRequestHttpException('Description is required.');
        }

        try {
            $wishlistItem = $this->wishlistItemService->addWishlistItemToWishlist($wishlist, $data);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Error: ' . $e->getMessage());
        }

        return new JsonResponse(['id' => $wishlistItem->getId()], Response::HTTP_CREATED);
    }
}
