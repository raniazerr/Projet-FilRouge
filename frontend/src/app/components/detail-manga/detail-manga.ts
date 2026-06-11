import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ActivatedRoute } from '@angular/router';
import { ChangeDetectorRef } from '@angular/core';
import { ServManga } from '../../services/MangaService';

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

  manga: MangaDetail | null = null;
  tomeSelectionne: Tome | null = null;
  chargement = true;
  erreur = false;

  constructor(
    private route: ActivatedRoute,
    private mangaService: ServManga,
    private cdr: ChangeDetectorRef
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
        this.tomeSelectionne = data.tomes?.[0] ?? null;
        this.chargement = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.erreur = true;
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });
  }

  selectionnerTome(tome: Tome): void {
    this.tomeSelectionne = tome;
  }

  get estEnStock(): boolean {
    return (this.tomeSelectionne?.stock ?? 0) > 0;
  }
}