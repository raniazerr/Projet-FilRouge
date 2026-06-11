import { Component } from '@angular/core';
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

  constructor(private authService: AuthService, private router: Router) {}

inscrire(): void {
  this.erreur = '';
  this.chargement = true;

  this.authService.register({ email: this.email, password: this.password, nom: this.nom, prenom: this.prenom }).subscribe({
    next: () => {
      // Connexion automatique après inscription
      this.authService.login(this.email, this.password).subscribe({
        next: () => {
          this.router.navigate(['/']);
        },
        error: () => {
          // Inscription ok mais login raté, on redirige quand même vers login
          this.router.navigate(['/login']);
        }
      });
    },
    error: (err) => {
      this.erreur = err.error?.error ?? 'Une erreur est survenue.';
      this.chargement = false;
    }
  });
}
}