<?php
namespace App\Controller;

use App\Entity\Child;
use App\Entity\Account;
use App\Entity\Household;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteHouseholdController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(
        #[MapEntity(id: 'householdId')] Household $household
    ): JsonResponse
    {
        $this->entityManager->remove($household);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Household deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

}
