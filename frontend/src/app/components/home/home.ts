import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { ServManga, Manga } from '../../services/MangaService';
import { ChangeDetectorRef } from '@angular/core'; // Mécanisme Angular qui force la mise à jour de l'affichage.

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, RouterModule],
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

  // Ordre de tri par popularité sélectionné actuellement
  triPopulariteActif: 'asc' | 'desc' | null = null;

  // Terme de recherche actuellement appliqué (vient de la navbar via l'URL)
  rechercheActive = '';

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

  constructor(
    private mangaService: ServManga,
    private cdr: ChangeDetectorRef,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    // Au démarrage du composant, on récupère les mangas via le service
    this.mangaService.getMangas().subscribe({
      next: (data) => {
        this.tousLesMangas = data.mangas;
        this.mangas = data.mangas;

        // Extraction des genres uniques et tri alphabétique
        this.genres = [...new Set(data.mangas.flatMap(m => m.genres ?? []))].sort();

        this.chargement = false;

        // Applique la recherche en cours (si l'URL contient déjà ?q=...)
        this.appliquerRecherche(this.rechercheActive);

        this.cdr.detectChanges();
      },
      error: (err) => {
        // Si le chargement échoue, on affiche une erreur
        this.erreur = true;
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });

    // Écoute le paramètre ?q= dans l'URL, à chaque changement (recherche depuis la navbar)
    this.route.queryParams.subscribe(params => {
      this.rechercheActive = params['q'] || '';
      if (this.tousLesMangas.length > 0) {
        this.appliquerRecherche(this.rechercheActive);
      }
      this.cdr.detectChanges();
    });
  }

  // Filtre la liste par titre (recherche insensible à la casse), remplace les autres filtres actifs
  appliquerRecherche(terme: string): void {
    if (!terme) {
      this.mangasFiltres = [...this.tousLesMangas];
      return;
    }

    this.triGenre = '';
    this.triPopulariteActif = null;

    const termeNormalise = terme.toLowerCase();
    this.mangasFiltres = this.tousLesMangas.filter(m =>
      m.titre.toLowerCase().startsWith(termeNormalise)
    );
  }

  // Texte affiché sur le bouton de tri par popularité, dépend du tri actif
  get labelPopularite(): string {
    if (this.triPopulariteActif === 'desc') return 'Plus réservé';
    if (this.triPopulariteActif === 'asc') return 'Moins réservé';
    return 'Par popularité';
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
    this.triPopulariteActif = null;
    this.dropdownGenreOuvert = false;
    this.mangasFiltres = this.tousLesMangas.filter(m => m.genres?.includes(genre));
  }

  trierParPopularite(ordre: 'asc' | 'desc'): void {
    this.dropdownPopOuvert = false;
    this.triPopulariteActif = ordre; // on garde en mémoire la sélection

    this.mangaService.getMangas('popularite').subscribe({
      next: (data) => {
        this.mangasFiltres = ordre === 'asc' ? [...data.mangas].reverse() : data.mangas;
        this.cdr.detectChanges();
      },
      error: () => {
        this.erreur = true;
        this.cdr.detectChanges();
      }
    });
  }

  reinitialiser(): void {
    // Réinitialise les filtres et restaure la liste complète
    this.triGenre = '';
    this.triPopulariteActif = null;
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