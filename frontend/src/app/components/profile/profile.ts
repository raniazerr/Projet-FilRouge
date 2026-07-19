import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ServUser, UserProfile } from '../../services/UserService';
import { Router } from '@angular/router';
import { AuthService } from '../../services/AuthService';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './profile.html',
  styleUrl: './profile.scss'
})
export class ProfileComponent implements OnInit {
  profil: UserProfile | null = null;
  reservations: any[] = [];

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

  constructor(private servUser: ServUser, private cdr: ChangeDetectorRef, private authService: AuthService, private router: Router) {}

  ngOnInit(): void {
    this.chargerProfil();
    this.chargerReservations();
  }

  chargerProfil(): void {
    this.servUser.getProfile().subscribe({
      next: (data) => {
        this.profil = data;
        this.nom = data.nom;
        this.prenom = data.prenom;
        this.email = data.email;
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.errorMessage = 'Impossible de charger le profil.';
        this.isLoading = false;
        this.cdr.detectChanges();
      }
    });
  }

  chargerReservations(): void {
    this.servUser.getHistoriqueCommandes().subscribe({
      next: (data) => {
        this.reservations = data;
        this.cdr.detectChanges();
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les réservations.';
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
}