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

class RegistrationController extends AbstractController
{
    // Contrôleur d'inscription.
    // Il vérifie les données envoyées, crée un nouvel utilisateur
    // et le sauvegarde avec un rôle utilisateur par défaut.

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['email'] ?? '') || empty($data['password'] ?? '') || empty($data['nom'] ?? '') || empty($data['prenom'] ?? '')) {
            return new JsonResponse(['error' => 'Les champs email, password, nom et prenom sont requis'], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['password']));
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setDateInscription(new \DateTimeImmutable());
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse($this->normalizeUser($user), Response::HTTP_CREATED);
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
