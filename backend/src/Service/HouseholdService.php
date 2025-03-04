<?php

namespace App\Service;

use App\Entity\Child;
use App\Entity\Household;
use App\Repository\HouseholdRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChildRepository;

class HouseholdService
{
    private EntityManagerInterface $entityManager;
    private HouseholdRepository $householdRepository;
    private ChildService $childService;

    public function __construct(EntityManagerInterface $entityManager, ChildService $childService, HouseholdRepository $householdRepository)
    {
        $this->entityManager = $entityManager;
        $this->householdRepository = $householdRepository;
        $this->childService = $childService;
    }

    public function createHousehold(string $name, string $description): Household
    {
        $household = new Household();
        $household->setName($name);
        $household->setDescription($description);

        $this->entityManager->persist($household);
        $this->entityManager->flush();

        return $household;
    }

    public function addChildToHousehold(int $householdId, string $childName)
    {
        $household = $this->householdRepository->find($householdId);
        if (!$household) {
            throw new \Exception('Household not found');
        }

        $child = $this->childService->createChild($household, $childName);
        $household->addChild($child);

        $this->entityManager->flush();

        return $child;
    }
}
