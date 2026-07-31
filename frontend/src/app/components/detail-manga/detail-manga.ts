import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ActivatedRoute } from '@angular/router';
import { ChangeDetectorRef } from '@angular/core';
import { ServManga } from '../../services/MangaService';
import { PanierService } from '../../services/PanierService';
import { ServUser } from '../../services/UserService';
import { FavoriService, Favori } from '../../services/FavoriService';
import { AuthService } from '../../services/AuthService';

export interface Tome {
  id: number;
  numero_tome: number;
  stock: number;
  prix: number;
}

export interface MangaDetail {
  id: number;
  api_id: number;
  titre: string;
  description: string;
  image: string;
  auteurs: string[];
  volumes: number | null;
  statut: string | null;
  tomes: Tome[];
}

@Component({
  selector: 'app-manga-detail',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './detail-manga.html',
  styleUrl: './detail-manga.scss'
})
export class MangaDetailComponent implements OnInit {
  // Ce composant affiche la page détaillée d'un manga.
  // Il récupère les informations du manga, gère la sélection d'un tome,
  // l'ajout au panier et le suivi des favoris de l'utilisateur.

  manga: MangaDetail | null = null;
  tomeSelectionne: Tome | null = null;
  chargement = true;
  erreur = false;
  userId: number | null = null;
  estFavori = false;
  favoriId: number | null = null;

  constructor(
    private route: ActivatedRoute,
    private mangaService: ServManga,
    private panierService: PanierService,
    private servUser: ServUser,
    private favoriService: FavoriService,
    private cdr: ChangeDetectorRef,
    public authService: AuthService
  ) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (!id) {
      this.erreur = true;
      this.chargement = false;
      return;
    }

    this.mangaService.getMangaById(+id).subscribe({
      next: (data: MangaDetail) => {
        this.manga = data;
        // Sélectionne le premier tome disponible par défaut
        this.tomeSelectionne = null;
        this.chargement = false;
        this.verifierFavori();
        this.cdr.detectChanges();
      },
      error: () => {
        this.erreur = true;
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });
  }

chargementFavori = true;

verifierFavori(): void {
  this.servUser.getProfile().subscribe({
    next: (profil) => {
      this.userId = profil.id;
      this.favoriService.getFavoris().subscribe({
        next: (favoris) => {
          const favori = favoris.find(f => f.utilisateur === this.userId && f.manga === this.manga?.id);
          if (favori) {
            this.estFavori = true;
            this.favoriId = favori.id;
          }
          this.chargementFavori = false;
          this.cdr.detectChanges();
        },
        error: () => { this.chargementFavori = false; this.cdr.detectChanges(); }
      });
    },
    error: () => { this.chargementFavori = false; this.cdr.detectChanges(); }
  });
}

favoriEnCours = false;

toggleFavori(): void {
  if (this.favoriEnCours || this.chargementFavori || !this.manga || !this.userId) return;
  this.favoriEnCours = true;

  const wasFavori = this.estFavori;
  const oldFavoriId = this.favoriId;

  this.estFavori = !wasFavori;
  this.cdr.detectChanges();

  if (!wasFavori) {
    // AJOUT
    this.favoriService.ajouter(this.userId, this.manga.id).subscribe({
      next: (favori: Favori) => {
        this.favoriId = favori.id;
        this.favoriEnCours = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.estFavori = wasFavori;
        this.favoriEnCours = false;
        this.cdr.detectChanges();
      }
    });
  } else {
    // RETRAIT
    if (!oldFavoriId) { this.favoriEnCours = false; return; }
    this.favoriService.supprimer(oldFavoriId).subscribe({
      next: () => {
        this.favoriId = null;
        this.favoriEnCours = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.estFavori = wasFavori;
        this.favoriId = oldFavoriId;
        this.favoriEnCours = false;
        this.cdr.detectChanges();
      }
    });
  }
}

  getTomeById(event: Event): Tome {
  const id = +(event.target as HTMLSelectElement).value;
  return this.manga!.tomes.find(t => t.id === id)!;
}

  selectionnerTome(tome: Tome): void {
    this.tomeSelectionne = tome;
  }

  get estEnStock(): boolean {
    return (this.tomeSelectionne?.stock ?? 0) > 0;
  }

    messageSuccess = '';
    messageErreur = '';

  ajouterAuPanier(): void {
    if (!this.tomeSelectionne) {
      this.messageErreur = 'Veuillez sélectionner un tome.';
      setTimeout(() => this.messageErreur = '', 3000);
      return;
    }
    this.panierService.ajouterAuPanier(this.tomeSelectionne.id).subscribe({
      next: () => {
        this.messageSuccess = 'Tome ajouté au panier !';
        this.cdr.detectChanges();
        setTimeout(() => { this.messageSuccess = ''; this.cdr.detectChanges(); }, 3000);
      },
      error: () => {
        this.messageErreur = 'Erreur lors de l\'ajout au panier.';
        this.cdr.detectChanges();
        setTimeout(() => { this.messageErreur = ''; this.cdr.detectChanges(); }, 3000);
      }
    });
  }
}