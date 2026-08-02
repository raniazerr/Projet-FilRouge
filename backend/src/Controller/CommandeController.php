<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Reservation;
use App\Repository\CommandeRepository;
use App\Repository\TomeRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/commandes')]
class CommandeController extends AbstractController
{
    // Contrôleur de gestion des commandes utilisateur.
    // Il permet de créer une commande, d'ajouter des réservations,
    // de consulter l'historique, de supprimer un article ou de valider une commande.

    // GET /api/commandes — voir son panier (commande en attente)
    #[Route('', methods: ['GET'])]
    public function index(CommandeRepository $repo): JsonResponse
    {
        $commandes = $repo->findBy(['utilisateur' => $this->getUser()]);
        return new JsonResponse(array_map([$this, 'normalize'], $commandes));
    }

    // POST /api/commandes/ajouter — ajouter un tome au panier
    #[Route('/ajouter', methods: ['POST'])]
    public function ajouter(Request $request, TomeRepository $tomeRepo, CommandeRepository $commandeRepo, EntityManagerInterface $em): JsonResponse
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
        return new JsonResponse(['error' => 'Les administrateurs ne peuvent pas réserver de tomes'], Response::HTTP_FORBIDDEN);
    }

        $data = json_decode($request->getContent(), true);

    $em->beginTransaction();
    try {
        // Bloque la ligne tant que la transaction n'est pas finie,
        // empêchant deux requêtes concurrentes de lire le même stock en même temps
        $tome = $tomeRepo->find($data['tome_id'] ?? null, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
        if (!$tome) {
            $em->rollback();
            return new JsonResponse(['error' => 'Tome introuvable'], Response::HTTP_BAD_REQUEST);
        }

        // Compte les réservations actives (en attente ou soumises) sur ce tome
        $reservationsActives = $em->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(Reservation::class, 'r')
            ->join('r.commande', 'c')
            ->where('r.tome = :tome')
            ->andWhere('c.statut IN (:statuts)')
            ->setParameter('tome', $tome)
            ->setParameter('statuts', ['en attente', 'soumise'])
            ->getQuery()
            ->getSingleScalarResult();

        if ($reservationsActives >= $tome->getStock()) {
            $em->rollback();
            return new JsonResponse(['error' => 'Tome en rupture de stock'], Response::HTTP_BAD_REQUEST);
        }

        $commande = $commandeRepo->findOneBy([
            'utilisateur' => $this->getUser(),
            'statut' => 'en attente'
        ]);

        if (!$commande) {
            $commande = new Commande();
            $commande->setUtilisateur($this->getUser());
            $em->persist($commande);
        }

        $reservation = new Reservation();
        $reservation->setTome($tome);
        $reservation->setCommande($commande);

        $em->persist($reservation);
        $em->flush();
        $em->commit();

        return new JsonResponse($this->normalize($commande), Response::HTTP_CREATED);

    } catch (\Exception $e) {
        $em->rollback();
        throw $e;
    }
}

    // GET /api/commandes/historique — voir l'historique des commandes (client)
    #[Route('/historique', methods: ['GET'])]
    public function historique(CommandeRepository $repo): JsonResponse
    {
        $commandes = $repo->createQueryBuilder('c')
            ->where('c.utilisateur = :user')
            ->andWhere('c.statut != :panier')
            ->setParameter('user', $this->getUser())
            ->setParameter('panier', 'en attente')
            ->orderBy('c.date_commande', 'DESC')
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map([$this, 'normalize'], $commandes));
    }

    // DELETE /api/commandes/reservation/{id} — supprimer un tome du panier
    #[Route('/reservation/{id}', methods: ['DELETE'])]
    public function supprimerReservation(Reservation $reservation, EntityManagerInterface $em): JsonResponse
    {
        $commande = $reservation->getCommande();

        if ($commande->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($reservation);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // DELETE /api/commandes/{id} — annuler toute la commande
    #[Route('/{id}', methods: ['DELETE'])]
    public function annuler(Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        if ($commande->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($commande);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // PATCH /api/commandes/{id}/soumettre — client soumet sa commande
    #[Route('/{id}/soumettre', methods: ['PATCH'])]
    public function soumettre(Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        if ($commande->getUtilisateur() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $commande->setStatut('soumise');
        $em->flush();

        return new JsonResponse($this->normalize($commande));
    }
    
    //-----------ADMIN----------

    // GET /api/commandes/admin — voir toutes les commandes sans le panier
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', methods: ['GET'])]
    public function adminIndex(CommandeRepository $repo): JsonResponse
    {
        $commandes = $repo->createQueryBuilder('c')
            ->where('c.statut != :panier')
            ->setParameter('panier', 'en attente')
            ->orderBy('c.date_commande', 'DESC')
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map([$this, 'normalize'], $commandes));
    }

    // PATCH /api/commandes/admin/{id}/statut — confirmer ou expirer
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/{id}/statut', methods: ['PATCH'])]
    public function adminUpdateStatut(Commande $commande, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statut = $data['statut'] ?? null;

        if (!in_array($statut, ['confirmée', 'expirée'])) {
            return new JsonResponse(['error' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Si confirmée, on décrémente le stock de chaque tome
        if ($statut === 'confirmée') {
            foreach ($commande->getReservations() as $reservation) {
                $tome = $reservation->getTome();
                if ($tome->getStock() <= 0) {
                    return new JsonResponse(['error' => 'Tome ' . $tome->getNumeroTome() . ' en rupture de stock'], Response::HTTP_BAD_REQUEST);
                }
                $tome->setStock($tome->getStock() - 1);
            }
        }

        $commande->setStatut($statut);
        $em->flush();

        return new JsonResponse($this->normalize($commande));
    }

    private function normalize(Commande $c): array
    {
        return [
            'id' => $c->getId(),
            'statut' => $c->getStatut(),
            'date_commande' => $c->getDateCommande()?->format('Y-m-d H:i:s'),
            'utilisateur' => [
                'id' => $c->getUtilisateur()->getId(),
                'nom' => $c->getUtilisateur()->getNom(),
                'prenom' => $c->getUtilisateur()->getPrenom(),
                'email' => $c->getUtilisateur()->getEmail(),
            ],
            'reservations' => array_map(fn($r) => [
                'id' => $r->getId(),
                'tome' => [
                    'id' => $r->getTome()->getId(),
                    'numero_tome' => $r->getTome()->getNumeroTome(),
                    'prix' => $r->getTome()->getPrix(),
                    'stock' => $r->getTome()->getStock(),
                    'manga_titre' => $r->getTome()->getManga()?->getTitre(),
                    'manga_image' => $r->getTome()->getManga()?->getImage(),
                ]
            ], $c->getReservations()->toArray()),
            'total' => array_sum(array_map(fn($r) => $r->getTome()->getPrix(), $c->getReservations()->toArray()))
        ];
    }
}