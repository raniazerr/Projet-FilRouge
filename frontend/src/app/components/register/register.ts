import { ChangeDetectorRef, Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/AuthService';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './register.html',
  styleUrl: './register.scss'
})
export class InscriptionComponent {

  email = '';
  password = '';
  nom = '';
  prenom = '';
  erreur = '';
  chargement = false;

  constructor(private authService: AuthService, private router: Router, private cdr: ChangeDetectorRef) {}

inscrire(): void {
  this.erreur = '';

  if (!this.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
    this.erreur = 'Adresse email invalide.';
    return;
  }

  if (this.password.length < 8) {
    this.erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
    return;
  }

  this.chargement = true;

  this.authService.register({ email: this.email, password: this.password, nom: this.nom, prenom: this.prenom }).subscribe({
    next: () => {
      this.authService.login(this.email, this.password).subscribe({
        next: () => {
          this.router.navigate(['/']);
        },
        error: () => {
          this.router.navigate(['/login']);
        }
      });
    },
    error: (err) => {
      this.erreur = err.error?.error ?? 'Une erreur est survenue.';
      this.chargement = false;
      this.cdr.detectChanges();
    }
  });
}}