<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    // GET /api/profile — infos du compte connecté
    #[Route('', methods: ['GET'])]
    public function show(): JsonResponse
    {
        return new JsonResponse($this->normalize($this->getUser()));
    }

    // PATCH /api/profile — modifier nom / prenom / email / mot de passe
    #[Route('', methods: ['PATCH', 'PUT'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['nom'])) {
            $user->setNom((string) $data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom((string) $data['prenom']);
        }
        if (isset($data['email'])) {
            $user->setEmail((string) $data['email']);
        }

        // Changement de mot de passe : exige l'ancien mot de passe
        if (!empty($data['password'])) {
            if (empty($data['currentPassword']) || !$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                return new JsonResponse(['error' => 'Mot de passe actuel incorrect'], Response::HTTP_BAD_REQUEST);
            }
            $user->setPassword($passwordHasher->hashPassword($user, (string) $data['password']));
        }

        $em->flush();

        return new JsonResponse($this->normalize($user));
    }

    private function normalize($user): array
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