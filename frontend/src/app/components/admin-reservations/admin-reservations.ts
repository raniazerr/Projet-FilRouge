import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ServAdmin, CommandeAdmin } from '../../services/AdminService';

@Component({
  selector: 'app-admin-reservations',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-reservations.html',
  styleUrl: './admin-reservations.scss'
})

export class AdminReservationsComponent implements OnInit {
  commandes: CommandeAdmin[] = [];
  filtreStatut: string = 'toutes';

  isLoading = true;
  errorMessage = '';
  actionEnCours: number | null = null; // id de la commande en cours de traitement

  constructor(private servAdmin: ServAdmin, private cdr: ChangeDetectorRef) {}

  ngOnInit(): void {
    this.chargerCommandes();
  }

  chargerCommandes(): void {
    this.isLoading = true;
    this.servAdmin.getToutesCommandes().subscribe({
      next: (data) => {
        this.commandes = data;
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les commandes.';
        this.isLoading = false;
        this.cdr.detectChanges();
      }
    });
  }

  get commandesFiltrees(): CommandeAdmin[] {
    if (this.filtreStatut === 'toutes') {
      return this.commandes;
    }
    return this.commandes.filter(c => c.statut === this.filtreStatut);
  }

  changerFiltre(statut: string): void {
    this.filtreStatut = statut;
  }

  confirmer(commande: CommandeAdmin): void {
    this.majStatut(commande, 'confirmée');
  }

  expirer(commande: CommandeAdmin): void {
    this.majStatut(commande, 'expirée');
  }

  private majStatut(commande: CommandeAdmin, statut: 'confirmée' | 'expirée'): void {
    this.actionEnCours = commande.id;
    this.errorMessage = '';

    this.servAdmin.updateStatutCommande(commande.id, statut).subscribe({
      next: (updated) => {
        const index = this.commandes.findIndex(c => c.id === commande.id);
        if (index !== -1) {
          this.commandes[index] = updated;
        }
        this.actionEnCours = null;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.errorMessage = err.error?.error || 'Erreur lors de la mise à jour du statut.';
        this.actionEnCours = null;
        this.cdr.detectChanges();
      }
    });
  }
}