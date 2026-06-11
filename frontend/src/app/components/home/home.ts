import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ServManga, Manga } from '../../services/MangaService';
import { ChangeDetectorRef } from '@angular/core'; // Mécanisme Angular qui force la mise à jour de l'affichage.

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './home.html',
  styleUrl: './home.scss'
})
export class HomeComponent implements OnInit {

  // Liste de tous les mangas reçus depuis le service
  mangas: Manga[] = [];

  // Copie complète de tous les mangas afin de pouvoir réinitialiser les filtres
  tousLesMangas: Manga[] = [];

  // Liste des mangas affichés après application d'un filtre
  mangasFiltres: Manga[] = [];

  // Liste des genres disponibles pour le menu de filtrage
  genres: string[] = [];

  // Genre sélectionné actuellement
  triGenre = '';

  // Indique si le menu déroulant de filtrage par genre est ouvert
  dropdownGenreOuvert = false;

  // Indique si le menu déroulant de tri par popularité est ouvert
  dropdownPopOuvert = false;

  // Indique si les données sont en cours de chargement
  chargement = true;

  // Indique si une erreur est survenue lors du chargement
  erreur = false;

  // Index du slide actif dans le carrousel
  slideActif = 0;

  constructor(private mangaService: ServManga, private cdr: ChangeDetectorRef) {}

  ngOnInit(): void {
    // Au démarrage du composant, on récupère les mangas via le service
    this.mangaService.getMangas().subscribe({
      next: (data) => {
        this.tousLesMangas = data.mangas;
        this.mangasFiltres = [...data.mangas];
        this.mangas = data.mangas;

        // Extraction des genres uniques et tri alphabétique
        this.genres = [...new Set(data.mangas.flatMap(m => m.genres ?? []))].sort();

        this.chargement = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        // Si le chargement échoue, on affiche une erreur
        this.erreur = true;
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });
  }

  toggleDropdown(lequel: 'genre' | 'pop'): void {
    if (lequel === 'genre') {
      this.dropdownGenreOuvert = !this.dropdownGenreOuvert;
      this.dropdownPopOuvert = false;
    } else {
      this.dropdownPopOuvert = !this.dropdownPopOuvert;
      this.dropdownGenreOuvert = false;
    }
  }

  filtrerParGenre(genre: string): void {
    // Applique un filtre de genre et ferme le menu
    this.triGenre = genre;
    this.dropdownGenreOuvert = false;
    this.mangasFiltres = this.tousLesMangas.filter(m => m.genres?.includes(genre));
  }

  trierParPopularite(ordre: 'asc' | 'desc'): void {
    // Ferme le menu de tri par popularité (implémentation du tri à ajouter si besoin)
    this.dropdownPopOuvert = false;
  }

  reinitialiser(): void {
    // Réinitialise les filtres et restaure la liste complète
    this.triGenre = '';
    this.dropdownGenreOuvert = false;
    this.mangasFiltres = [...this.tousLesMangas];
  }

  prevSlide(): void {
    // Passe au slide précédent dans le carrousel
    this.slideActif = this.slideActif === 0 ? 2 : this.slideActif - 1;
  }

  nextSlide(): void {
    // Passe au slide suivant dans le carrousel
    this.slideActif = this.slideActif === 2 ? 0 : this.slideActif + 1;
  }

  get peutPrev(): boolean {
    // Indique si le bouton précédent doit être activé
    return this.slideActif > 0;
  }

  get peutNext(): boolean {
    // Indique si le bouton suivant doit être activé
    return this.slideActif < 2;
  }
}