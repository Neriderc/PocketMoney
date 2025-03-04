<?php
namespace App\Controller;

use App\Entity\Child;
use App\Entity\User;
use App\Service\ChildService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddTransactionScheduleController extends AbstractController
{
    private ChildService $childService;
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, ChildService $childService, Security $security)
    {
        $this->childService = $childService;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function __invoke(Request $request, $childId): JsonResponse
    {
        $child = $this->entityManager->getRepository(Child::class)->find($childId);

        $user = $this->security->getUser();

        if (!$user instanceof User || !$child) {
            throw new BadRequestHttpException('Failed to get entities.');
        }

        if (
            !in_array('ROLE_ADMIN', $user->getRoles(), true) &&
            !$child->getHousehold()->getUsers()->contains($user)
        ) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['description'])) {
            throw new BadRequestHttpException('Description is required.');
        }

        try {
            $transactionSchedule = $this->childService->addTransactionScheduleToChild($childId, $data);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Error: ' . $e->getMessage());
        }

        return new JsonResponse(['id' => $transactionSchedule->getId()], Response::HTTP_CREATED);
    }
}
