<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Controller;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/api/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $emailRaw = $body['email'] ?? '';
        $password = $body['password'] ?? '';

        if ($emailRaw === '' || $password === '') {
            return $this->json(['error' => 'Email and password are required.'], 400);
        }

        try {
            $email = Email::fromString($emailRaw);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'Invalid email format.'], 400);
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Invalid credentials.'], 401);
        }

        return $this->json([
            'token' => $user->getApiToken(),
            'user'  => [
                'id'       => $user->getId()->value,
                'name'     => $user->getName(),
                'username' => $user->getUsername(),
                'email'    => $user->getEmail()->value,
                'role'     => $user->getRole()->value,
            ],
        ]);
    }
}
