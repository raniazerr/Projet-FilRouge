import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { PanierService, Commande } from '../../services/PanierService';

@Component({
  selector: 'app-panier',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './panier.html',
  styleUrl: './panier.scss'
})
export class PanierComponent implements OnInit {
  // Ce composant affiche le panier de l'utilisateur.
  // Il charge la commande en attente, permet de supprimer des réservations,
  // d'annuler une commande ou de la valider.

  commande: Commande | null = null;
  chargement = true;
  erreur = '';

  constructor(
    private panierService: PanierService,
    private cdr: ChangeDetectorRef,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.chargerPanier();
  }

  get lignesPanier() {
  if (!this.commande) return [];

  const groupes = new Map<number, { tome: any; ids: number[] }>();

  for (const r of this.commande.reservations) {
    const tomeId = r.tome.id;
    if (!groupes.has(tomeId)) {
      groupes.set(tomeId, { tome: r.tome, ids: [] });
    }
    groupes.get(tomeId)!.ids.push(r.id);
  }

  return Array.from(groupes.values());
}
  chargerPanier(): void {
    this.panierService.getCommandes().subscribe({
      next: (data) => {
        // On prend la commande en attente s'il y en a une
        this.commande = data.find(c => c.statut === 'en attente') ?? null;
        this.chargement = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.erreur = 'Impossible de charger le panier.';
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });
  }

  supprimerUnExemplaire(reservationId: number): void {
    this.panierService.supprimerReservation(reservationId).subscribe({
      next: () => {
        if (this.commande) {
          this.commande.reservations = this.commande.reservations.filter(r => r.id !== reservationId);
          this.commande.total = this.commande.reservations.reduce((sum, r) => sum + r.tome.prix, 0);
          this.cdr.detectChanges();
        }
      }
    });
  }

  annuler(): void {
    if (!this.commande) return;
    this.panierService.annulerCommande(this.commande.id).subscribe({
      next: () => {
        this.commande = null;
        this.cdr.detectChanges();
      }
    });
  }

  valider(): void {
  if (!this.commande) return;
  this.panierService.soumettre(this.commande.id).subscribe({
    next: () => {
      this.commande = null;
      this.cdr.detectChanges();
      this.router.navigate(['/']);
    }
  });
  }
}