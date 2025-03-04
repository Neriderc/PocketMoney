<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\InvalidRoleException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    const VALID_ROLES = ['ROLE_USER', 'ROLE_ADMIN'];
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    public function createUser(string $username, string $password, array $roles = ['ROLE_USER']): ?User
    {
        if ($this->entityManager->getRepository(User::class)->findOneBy(['username' => $username])) {
            return null;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return null;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUserRoles(mixed $username, array $roles)
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw new UserNotFoundException("User with username '$username' not found.");
        }

        if ($this->validateUserRoles($roles)) {
            $user->setRoles($roles);
            $this->entityManager->flush();
        } else {
            throw new InvalidRoleException(self::VALID_ROLES);
        }
        return true;
    }

    private function validateUserRoles($roles)
    {
        foreach ($roles as $role) {
            if (!in_array($role, self::VALID_ROLES, true)) {
                return false;
            }
        }
        return true;
    }
}