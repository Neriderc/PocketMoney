<?php

namespace App\Controller;

use App\Entity\Child;
use App\Entity\User;
use App\Service\ChildService;
use App\Service\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class AddAccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AccountService $accountService;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, AccountService $accountService, SerializerInterface $serializer, private readonly Security $security)
    {
        $this->entityManager = $entityManager;
        $this->accountService = $accountService;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, #[MapEntity(id: 'childId')] Child $child): Response
    {
        $user = $this->security->getUser();

        $hasAccess =
            $user instanceof User &&
            (
                $user->isAdmin() ||
                $user === $child->getLinkedUser() ||
                $user->getHouseholds()->contains($child->getHousehold())
            );

        if (!$hasAccess) {
            throw $this->createAccessDeniedException('You do not have access to this household.');
        }

        $data = json_decode($request->getContent(), true);

        $account = $this->accountService->createAccount($child, $data);
        $child->addAccount($account);
        $this->entityManager->flush();

        return $this->json($account, Response::HTTP_CREATED, [], ['groups' => 'accounts:list']);

    }
}
