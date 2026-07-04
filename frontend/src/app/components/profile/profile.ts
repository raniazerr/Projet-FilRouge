import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ServUser, UserProfile } from '../../services/UserService';

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

  constructor(private servUser: ServUser, private cdr: ChangeDetectorRef) {}

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
        this.cdr.detectChanges();
      },
      error: () => {
        this.errorMessage = 'Impossible de charger le profil.';
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

  enregistrer(): void {
    this.successMessage = '';
    this.errorMessage = '';

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
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.errorMessage = err.error?.error || 'Erreur lors de la mise à jour.';
        this.cdr.detectChanges();
      }
    });
  }
}