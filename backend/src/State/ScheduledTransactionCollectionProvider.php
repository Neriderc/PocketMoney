<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Child;
use App\Entity\User;
use App\Entity\Household;
use App\Repository\ChildRepository;
use App\Repository\ScheduledTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ScheduledTransactionCollectionProvider implements ProviderInterface
{
    private ChildRepository $childRepository;
    private ScheduledTransactionRepository $scheduledTransactionRepository;

    public function __construct(
        private Security $security,
        ChildRepository $childRepository,
        ScheduledTransactionRepository $scheduledTransactionRepository,
    ) {
        $this->childRepository = $childRepository;
        $this->scheduledTransactionRepository = $scheduledTransactionRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $child = $this->childRepository->find($uriVariables['childId']);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->scheduledTransactionRepository->findBy(['child' => $child]);
        }

        if ($child->getHousehold()->getUsers()->contains($user) ||
            $child->getLinkedUser() === $user
        ) {
            return $this->scheduledTransactionRepository->findBy(['child' => $child]);
        }

        throw new AccessDeniedHttpException();
    }
}