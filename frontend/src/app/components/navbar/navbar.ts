import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/AuthService';
import { ServManga, Manga } from '../../services/MangaService';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './navbar.html',
  styleUrl: './navbar.scss'
})
export class NavbarComponent implements OnInit {
  // Ce composant gère la barre de navigation en haut du site.
  // Il permet de lancer une recherche, d'afficher des suggestions
  // et de rediriger vers la page du manga sélectionné.
  searchQuery = '';
  panierCount = 0;
  connecte$;

  // Catalogue complet, chargé une fois, utilisé pour les suggestions
  tousLesMangas: Manga[] = [];
  suggestions: Manga[] = [];
  suggestionsVisibles = false;

  constructor(
    public authService: AuthService,
    private router: Router,
    private mangaService: ServManga
  ) {
    this.connecte$ = this.authService.connecte$;
  }

  ngOnInit(): void {
    this.mangaService.getMangas().subscribe({
      next: (data) => this.tousLesMangas = data.mangas,
      error: () => {} // pas grave si ça échoue, juste pas de suggestions
    });
  }

  // Appelé à chaque frappe : met à jour la liste de suggestions
  onSaisie(): void {
    const terme = this.searchQuery.trim().toLowerCase();

    if (!terme) {
      this.suggestions = [];
      this.suggestionsVisibles = false;
      return;
    }

    this.suggestions = this.tousLesMangas
      .filter(m => m.titre.toLowerCase().startsWith(terme))
      .slice(0, 8);

    this.suggestionsVisibles = this.suggestions.length > 0;
  }

  // Petit délai pour laisser le clic sur une suggestion s'exécuter avant que le champ perde le focus
  fermerSuggestions(): void {
    setTimeout(() => this.suggestionsVisibles = false, 150);
  }

  choisirSuggestion(manga: Manga): void {
    this.searchQuery = '';
    this.suggestions = [];
    this.suggestionsVisibles = false;
    this.router.navigate(['/manga', manga.id]);
  }

  rechercher(): void {
    this.suggestionsVisibles = false;
    this.router.navigate(['/'], {
      queryParams: { q: this.searchQuery.trim() || null }
    });
  }
}