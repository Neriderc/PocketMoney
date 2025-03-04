<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use App\Entity\Household;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

final class HouseholdCollectionProvider implements ProviderInterface
{
    public function __construct(
        private CollectionProvider $collectionProvider,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $households = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return [];
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $households;
        }

        $householdsArray = iterator_to_array($households, false);

        return array_filter($householdsArray, fn(Household $household) =>
        $household->getUsers()->contains($user)
        );
    }
}