<?php
namespace App\Controller;

use App\Entity\Child;
use App\Entity\Household;
use App\Entity\User;
use App\Service\HouseholdService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddChildController extends AbstractController
{
    private HouseholdService $householdService;
    private $security;
    private $em;

    public function __construct(HouseholdService $householdService, Security $security, EntityManagerInterface $em)
    {
        $this->householdService = $householdService;
        $this->security = $security;
        $this->em = $em;
    }

    public function __invoke(Request $request, int $householdId): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User || !$householdId) {
            throw new BadRequestHttpException('Failed to get entities.');
        }


        $household = $this->em->getRepository(Household::class)->find($householdId);
        if (!$household) {
            throw new NotFoundHttpException('Household not found.');
        }

        if (
            !in_array('ROLE_ADMIN', $user->getRoles(), true) &&
            !$household->getUsers()->contains($user)
        ) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            throw new BadRequestHttpException('Child name is required.');
        }

        $childName = $data['name'];
        $dateOfBirth = null;
        if (!empty($data['dateOfBirth'])) {
            $dateOfBirth = new \DateTimeImmutable($data['dateOfBirth']);
        }
        try {
            $child = $this->householdService->addChildToHousehold($householdId, $childName);
            if ($dateOfBirth) {
            $child->setDateOfBirth($dateOfBirth);
            }
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Error: ' . $e->getMessage());
        }

        return new JsonResponse(['id' => $child->getId(), 'name' => $child->getName()], Response::HTTP_CREATED);
    }
}
