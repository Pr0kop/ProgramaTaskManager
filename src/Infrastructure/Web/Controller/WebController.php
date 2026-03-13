<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/web')]
final class WebController extends AbstractController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/login', name: 'web_login', methods: ['GET'])]
    public function loginForm(Request $request): Response
    {
        if ($request->getSession()->get('web_user_id')) {
            return $this->redirectToRoute('web_dashboard');
        }

        return $this->render('web/login.html.twig');
    }

    #[Route('/login', name: 'web_login_post', methods: ['POST'])]
    public function loginSubmit(Request $request): Response
    {
        $emailRaw = trim($request->request->get('email', ''));
        $password = $request->request->get('password', '');

        try {
            $email = Email::fromString($emailRaw);
        } catch (\InvalidArgumentException) {
            return $this->render('web/login.html.twig', ['error' => 'Nieprawidłowy adres email.']);
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->render('web/login.html.twig', ['error' => 'Błędny email lub hasło.']);
        }

        $session = $request->getSession();
        $session->set('web_user_id', $user->getId()->value);

        return $this->redirectToRoute('web_dashboard');
    }

    #[Route('/dashboard', name: 'web_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $userId = $request->getSession()->get('web_user_id');

        if (!$userId) {
            return $this->redirectToRoute('web_login');
        }

        $user = $this->userRepository->findById(UserId::fromString($userId));

        if ($user === null) {
            $request->getSession()->clear();
            return $this->redirectToRoute('web_login');
        }

        $tasks = $user->isAdmin()
            ? $this->taskRepository->findAll()
            : $this->taskRepository->findByUserId($user->getId());

        $taskData = array_map(function ($task) {
            $assignedUser = null;
            if ($task->getAssignedUserId() !== null) {
                $assignedUser = $this->userRepository->findById($task->getAssignedUserId());
            }

            return [
                'id'           => $task->getId()->value,
                'title'        => $task->getTitle(),
                'description'  => $task->getDescription(),
                'status'       => $task->getStatus()->value,
                'assignedUser' => $assignedUser?->getName(),
                'createdAt'    => $task->getCreatedAt()->format('d.m.Y H:i'),
            ];
        }, $tasks);

        return $this->render('web/dashboard.html.twig', [
            'user'      => $user,
            'tasks'     => $taskData,
            'taskCount' => count($taskData),
        ]);
    }

    #[Route('/logout', name: 'web_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('web_login');
    }
}
