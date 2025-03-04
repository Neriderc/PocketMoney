<?php
namespace App\Controller;

use App\Entity\Child;
use App\Entity\Account;
use App\Entity\User;
use App\Service\TransactionScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class GetAccountsController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    ) {}

    public function __invoke(
        #[MapEntity(id: 'childId')] Child $child,
        TransactionScheduleService $transactionScheduleService,
    ): JsonResponse {
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

        $transactionScheduleService->processTransactionsForChild($child);

        $accounts = $child->getAccounts()->toArray();

        $accountsWithId = array_map(function ($account) use ($child) {
            return [
                '@id' => '/api/children/' . $child->getId() . '/accounts/' . $account->getId(),
                '@type' => 'Account',
                'id' => $account->getId(),
                'name' => $account->getName(),
                'balance' => $account->getBalance(),
                'icon' => $account->getIcon(),
                'color' => $account->getColor(),
            ];
        }, $accounts);

        $responseData = [
            '@context' => '/api/contexts/Child',
            '@id' => '/api/children/' . $child->getId() . '/accounts',
            '@type' => 'Collection',
            'totalItems' => count($accounts),
            'member' => [
                [
                    '@id' => '/api/children/' . $child->getId(),
                    '@type' => 'Child',
                    'accounts' =>  $accountsWithId
                ]
            ]
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
