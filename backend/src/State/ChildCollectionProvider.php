<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Entity\Child;
use App\Entity\User;
use App\Entity\Household;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ChildCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();
        $householdId = $uriVariables['householdId'] ?? null;

        if (!$user instanceof User || !$householdId) {
            return [];
        }

        $household = $this->em->getRepository(Household::class)->find($householdId);
        if (!$household) {
            throw new NotFoundHttpException('Household not found.');
        }

        if (
            in_array('ROLE_ADMIN', $user->getRoles(), true) ||
            $household->getUsers()->contains($user)
        ) {
            return $this->em->getRepository(Child::class)->findBy(['household' => $household]);
        }

        $linkedChild = $this->em->getRepository(Child::class)->findOneBy([
            'linkedUser' => $user,
            'household' => $household,
        ]);

        return $linkedChild ? [$linkedChild] : throw new AccessDeniedException('You do not have access to this household.');;
    }
}