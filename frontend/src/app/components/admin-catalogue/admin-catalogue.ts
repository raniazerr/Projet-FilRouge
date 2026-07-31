import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ServManga, Manga, MangaApiResult, Tome } from '../../services/MangaService';

@Component({
  selector: 'app-admin-catalogue',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-catalogue.html',
  styleUrl: './admin-catalogue.scss'
})
export class AdminCatalogueComponent implements OnInit {
  // Ce composant permet à l'administrateur de gérer le catalogue.
  // Il sert à rechercher des mangas depuis l'API externe,
  // à les ajouter au catalogue local et à gérer leurs tomes.

  // Recherche externe
  recherche = '';
  resultatsApi: MangaApiResult[] = [];
  isSearching = false;
  ajoutEnCours: number | null = null; // api_id en cours d'ajout
  searchError = '';

  // Catalogue existant
  catalogue: Manga[] = [];
  isLoadingCatalogue = true;

  // Gestion des tomes
  mangaSelectionne: Manga | null = null;
  tomes: Tome[] = [];
  isLoadingTomes = false;

  // Formulaire nouveau tome
  nouveauTome = { numero_tome: null as number | null, prix: null as number | null, stock: 0 };
  isSavingTome = false;
  tomeError = '';

  constructor(private servManga: ServManga, private cdr: ChangeDetectorRef) {}

  ngOnInit(): void {
    this.chargerCatalogue();
  }

  chargerCatalogue(): void {
    this.isLoadingCatalogue = true;
    this.servManga.getMangas().subscribe({
      next: (data) => {
        this.catalogue = data.mangas;
        this.isLoadingCatalogue = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.isLoadingCatalogue = false;
        this.cdr.detectChanges();
      }
    });
  }

  // --- Recherche externe ---

  rechercher(): void {
    if (this.recherche.trim().length < 2) {
      this.searchError = 'Tape au moins 2 caractères.';
      return;
    }

    this.isSearching = true;
    this.searchError = '';

    this.servManga.searchMangaApi(this.recherche).subscribe({
      next: (data) => {
        this.resultatsApi = data;
        this.isSearching = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.searchError = 'Erreur lors de la recherche.';
        this.isSearching = false;
        this.cdr.detectChanges();
      }
    });
  }

  dejaAuCatalogue(apiId: number): boolean {
    return this.catalogue.some(m => m.api_id === apiId);
  }

  ajouterAuCatalogue(resultat: MangaApiResult): void {
    this.ajoutEnCours = resultat.api_id;

    this.servManga.ajouterMangaAuCatalogue(resultat.api_id).subscribe({
      next: (manga) => {
        this.catalogue.push(manga);
        this.ajoutEnCours = null;
        this.selectionnerManga(manga);
        this.cdr.detectChanges();
      },
      error: () => {
        this.searchError = "Erreur lors de l'ajout au catalogue.";
        this.ajoutEnCours = null;
        this.cdr.detectChanges();
      }
    });
  }

  // --- Gestion des tomes ---

  selectionnerManga(manga: Manga): void {
    this.mangaSelectionne = manga;
    this.tomes = [];
    this.nouveauTome = { numero_tome: null, prix: null, stock: 0 };
    this.tomeError = '';
    this.chargerTomes(manga.id);
  }

  fermerSelection(): void {
    this.mangaSelectionne = null;
    this.tomes = [];
  }

  chargerTomes(mangaId: number): void {
    this.isLoadingTomes = true;
    this.servManga.getTomesByManga(mangaId).subscribe({
      next: (data) => {
        this.tomes = data;
        this.isLoadingTomes = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.isLoadingTomes = false;
        this.cdr.detectChanges();
      }
    });
  }

  ajouterTome(): void {
    if (!this.mangaSelectionne || !this.nouveauTome.numero_tome) {
      this.tomeError = 'Le numéro du tome est requis.';
      return;
    }

    this.isSavingTome = true;
    this.tomeError = '';

    this.servManga.creerTome({
      manga_id: this.mangaSelectionne.id,
      numero_tome: this.nouveauTome.numero_tome,
      prix: this.nouveauTome.prix ?? 0,
      stock: this.nouveauTome.stock ?? 0
    }).subscribe({
      next: (tome) => {
        this.tomes.push(tome);
        this.nouveauTome = { numero_tome: null, prix: null, stock: 0 };
        this.isSavingTome = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.tomeError = err.error?.error || "Erreur lors de l'ajout du tome.";
        this.isSavingTome = false;
        this.cdr.detectChanges();
      }
    });
  }

  supprimerTome(tome: Tome): void {
    this.servManga.supprimerTome(tome.id).subscribe({
      next: () => {
        this.tomes = this.tomes.filter(t => t.id !== tome.id);
        this.cdr.detectChanges();
      },
      error: () => {
        this.tomeError = 'Erreur lors de la suppression du tome.';
        this.cdr.detectChanges();
      }
    });
  }
}