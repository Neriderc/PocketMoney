<?php
namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Service\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddTransactionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private AccountService $accountService;

    public function __construct(EntityManagerInterface $entityManager, Security $security, AccountService $accountService)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->accountService = $accountService;
    }

    public function __invoke(Request $request, $accountId): JsonResponse
    {
        $accountId = $this->entityManager->getRepository(Account::class)->find($accountId);

        $user = $this->security->getUser();

        if (!$user instanceof User || !$accountId) {
            throw new BadRequestHttpException('Failed to get entities.');
        }

        if (
            !in_array('ROLE_ADMIN', $user->getRoles(), true) &&
            !$accountId->getChild()->getHousehold()->getUsers()->contains($user)
        ) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['description'])) {
            throw new BadRequestHttpException('Description is required.');
        }

        try {
            $transaction = $this->accountService->addTransactionToAccount($accountId, $data);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Error: ' . $e->getMessage());
        }

        return new JsonResponse(['id' => $transaction->getId()], Response::HTTP_CREATED);
    }
}
