<?php
namespace App\Controller;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Household;
use App\Entity\User;
use App\Service\HouseholdService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddUserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request, IriConverterInterface $iriConverter, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new BadRequestHttpException('Failed to get entity.');
        }

        if (
            !in_array('ROLE_ADMIN', $user->getRoles(), true)
        ) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);

        try {
            $user = $this->userService->createUser(
                $data['username'],
                $data['password'],
                $data['roles'] ?? ['ROLE_USER']
            );

            if ($user === null) {
                return $this->json(['error' => "Failed to create user"], 400);
            }

            $user->setEmail($data['email']);

            foreach ($data['households'] ?? [] as $householdIri) {
                $household = $iriConverter->getResourceFromIri($householdIri);

                if ($household instanceof Household) {
                    $user->addHousehold($household);
                } else {
                    return $this->json(['error' => "Invalid household: $householdIri"], 400);
                }
            }
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json($user, 201);
        } catch (ValidationException $e) {
            return $this->json(['errors' => (string) $e], 400);
        }
    }
}
