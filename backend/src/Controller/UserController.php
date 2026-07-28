<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = array_map([$this, 'normalizeUser'], $userRepository->findAll());

        return new JsonResponse($users);
    }

    #[Route('/new', name: 'app_user_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['email'] ?? '') || empty($data['nom'] ?? '') || empty($data['prenom'] ?? '') || empty($data['password'] ?? '')) {
            return new JsonResponse(['error' => 'Les champs email, password, nom et prenom sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail((string) $data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, (string) $data['password']));
        $user->setNom((string) $data['nom']);
        $user->setPrenom((string) $data['prenom']);
        $user->setDateInscription(new \DateTimeImmutable());
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse($this->normalizeUser($user), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->normalizeUser($user));
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, ?User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['email'])) {
            $user->setEmail((string) $data['email']);
        }
        if (isset($data['nom'])) {
            $user->setNom((string) $data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom((string) $data['prenom']);
        }
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, (string) $data['password']));
        }

        $entityManager->flush();

        return new JsonResponse($this->normalizeUser($user));
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(?User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function normalizeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'date_inscription' => $user->getDateInscription()?->format('Y-m-d H:i:s'),
            'roles' => $user->getRoles(),
        ];
    }
}
