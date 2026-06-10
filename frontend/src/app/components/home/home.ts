import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ServManga, Manga } from '../../services/MangaService';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './home.html',
  styleUrl: './home.scss'
})
export class HomeComponent implements OnInit {

  mangas: Manga[] = [];
  top3: any[] = [];
  chargement = true;
  erreur = false;

  constructor(private mangaService: ServManga) {}

  ngOnInit(): void {
    this.mangaService.getMangas().subscribe({
      next: (data) => {
        this.mangas = data;
        this.chargement = false;
      },
      error: () => {
        this.erreur = true;
        this.chargement = false;
      }
    });
  }
}