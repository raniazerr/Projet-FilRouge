import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ServUser, UserProfile } from '../../services/UserService';
import { Router, RouterModule, ActivatedRoute } from '@angular/router';
import { AuthService } from '../../services/AuthService';
import { ServManga, Manga } from '../../services/MangaService';
import { FavoriService } from '../../services/FavoriService';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './profile.html',
  styleUrl: './profile.scss'
})
export class ProfileComponent implements OnInit {
  // Ce composant affiche et met à jour le profil utilisateur.
  // Il charge les informations du compte, les réservations et les favoris.

  profil: UserProfile | null = null;
  reservations: any[] = [];
  favoris: { favoriId: number; manga: any }[] = [];

  // form fields
  nom = '';
  prenom = '';
  email = '';
  password = '';
  currentPassword = '';

  successMessage = '';
  errorMessage = '';

  isLoading = true;
  isSaving = false;

  constructor(
    private servUser: ServUser,
    private mangaService: ServManga,
    private cdr: ChangeDetectorRef,
    public authService: AuthService,
    private router: Router,
    private favoriService: FavoriService
  ) {}

  ngOnInit(): void {
    this.chargerProfil();
    if (!this.authService.isAdmin()) {
      this.chargerReservations();
    }
  }

  chargerProfil(): void {
    this.servUser.getProfile().subscribe({
      next: (data) => {
        this.profil = data;
        this.nom = data.nom;
        this.prenom = data.prenom;
        this.email = data.email;
        this.isLoading = false;
        if (!this.authService.isAdmin()) {
          this.chargerFavoris();
        }
        this.cdr.detectChanges();
      },
      error: () => {
        this.errorMessage = 'Impossible de charger le profil.';
        this.isLoading = false;
        this.cdr.detectChanges();
      }
    });
  }

chargementFavoris = true;
chargementReservations = true;

chargerReservations(): void {
  this.servUser.getHistoriqueCommandes().subscribe({
    next: (data) => {
      this.reservations = data;
      this.chargementReservations = false;
      this.cdr.detectChanges();
    },
    error: () => {
      this.errorMessage = 'Impossible de charger les réservations.';
      this.chargementReservations = false;
      this.cdr.detectChanges();
    }
  });
}

chargerFavoris(): void {
  this.favoriService.getMesFavoris().subscribe({
    next: (favoris) => {
      this.favoris = favoris.map(f => ({ favoriId: f.favoriId, manga: f.manga }));
      this.chargementFavoris = false;
      this.cdr.detectChanges();
    },
    error: (err) => {
      console.error('Erreur lors du chargement des favoris:', err);
      this.chargementFavoris = false;
      this.cdr.detectChanges();
    }
  });
}
  supprimerFavori(favoriId: number): void {
    this.favoriService.supprimer(favoriId).subscribe({
      next: () => {
        this.favoris = this.favoris.filter(f => f.favoriId !== favoriId);
        this.cdr.detectChanges();
      }
    });
  }

  deconnexion(): void {
    this.authService.logout();
   this.router.navigate(['/login']);
  }

  enregistrer(): void {
    this.successMessage = '';
    this.errorMessage = '';
    this.isSaving = true;

    const payload: any = {
      nom: this.nom,
      prenom: this.prenom,
      email: this.email
    };

    if (this.password) {
      payload.password = this.password;
      payload.currentPassword = this.currentPassword;
    }

    this.servUser.updateProfile(payload).subscribe({
      next: (data) => {
        this.profil = data;
        this.password = '';
        this.currentPassword = '';
        this.successMessage = 'Profil mis à jour.';
        this.isSaving = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.errorMessage = err.error?.error || 'Erreur lors de la mise à jour.';
        this.isSaving = false;
        this.cdr.detectChanges();
      }
    });
  }

  supprimerCompte(): void {
  if (!confirm('Supprimer définitivement votre compte ? Cette action est irréversible.')) {
    return;
  }
  this.servUser.deleteAccount().subscribe({
    next: () => {
      this.authService.logout();
      this.router.navigate(['/']);
    },
    error: () => {
      this.errorMessage = 'Impossible de supprimer le compte.';
      this.cdr.detectChanges();
    }
  });
}
} 