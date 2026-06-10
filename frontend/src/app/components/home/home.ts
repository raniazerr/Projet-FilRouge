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
  chargement = true;
  erreur = false;
  slideActif = 0;

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

  prevSlide(): void {
    this.slideActif = this.slideActif === 0 ? 2 : this.slideActif - 1;
  }

  nextSlide(): void {
    this.slideActif = this.slideActif === 2 ? 0 : this.slideActif + 1;
  }

  get peutPrev(): boolean {
    return this.slideActif > 0;
  }

  get peutNext(): boolean {
    return this.slideActif < 2;
  }
}