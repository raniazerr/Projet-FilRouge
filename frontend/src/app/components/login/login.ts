import { Component, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/AuthService';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class LoginComponent {

  email = '';
  password = '';
  erreur = '';
  chargement = false;

  constructor(
    private authService: AuthService,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) {}

  connexion(): void {
    this.erreur = '';
    this.chargement = true;

    this.authService.login(this.email, this.password).subscribe({
      next: () => {
        this.chargement = false;
        this.router.navigate(['/']);
      },
      error: () => {
        this.erreur = 'Email ou mot de passe incorrect.';
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });
  }
}