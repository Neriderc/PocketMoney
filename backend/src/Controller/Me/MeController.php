<?php
namespace App\Controller\Me;

use App\Entity\Household;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MeController extends AbstractController
{
    #[Route('/api/users/me', name: 'api_me', methods: ['GET'], priority: 10)]
    public function getMe(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('You must be logged in to access this endpoint.');
        }

        $data = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users/me', name: 'api_me_update', methods: ['PATCH'], priority: 10)]
    public function updateMe(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('You must be logged in to access this endpoint.');
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['defaultHouseholdId'])) {
            return $this->json(['error' => 'Missing defaultHouseholdId'], 400);
        }

        $household = $entityManager->getRepository(Household::class)->find($data['defaultHouseholdId']);
        if (!$household) {
            return $this->json(['error' => 'Household not found'], 404);
        }

        $user->setDefaultHousehold($household);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $entityManager->flush();

        $data = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, 200, [], true);
    }
}