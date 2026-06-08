import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { ServManga } from '../../services/MangaService';

interface Manga {
  id: number;
  api_id: number;
  titre: string;
  image: string;
}

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './home.html',
  styleUrl: './home.scss'
})

export class HomeComponent implements OnInit {

  constructor(private manga: ServManga) {}

  // private apiUrl = 'http://localhost:8000';

  // tousLesMangas: Manga[] = [];
  // mangasFiltres: Manga[] = [];

  // triGenre: string = '';
  // triPopularite: string = '';
  // dropdownGenreOuvert = false;
  // dropdownPopOuvert = false;
  // chargement = true;
  // erreur = false;

  // // Genres : à adapter selon ce que tu auras en BDD plus tard
  // genres: string[] = ['Action', 'Aventure', 'Thriller', 'Comédie'];

  // constructor(private http: HttpClient) {}

  ngOnInit(): void {
    // this.chargerMangas();
    this.manga.monapi();
  }

//   chargerMangas(): void {
//     this.chargement = true;
//     this.http.get<Manga[]>(`${this.apiUrl}/manga/index`).subscribe({
//       next: (data) => {
//         this.tousLesMangas = data;
//         this.mangasFiltres = [...data];
//         this.chargement = false;
//       },
//       error: () => {
//         this.erreur = true;
//         this.chargement = false;
//       }
//     });
//   }

//   get topMangas(): Mangaa[] {
//     return this.tousLesMangas.slice(0, 3);
//   }

//   toggleDropdown(lequel: 'genre' | 'pop'): void {
//     if (lequel === 'genre') {
//       this.dropdownGenreOuvert = !this.dropdownGenreOuvert;
//       this.dropdownPopOuvert = false;
//     } else {
//       this.dropdownPopOuvert = !this.dropdownPopOuvert;
//       this.dropdownGenreOuvert = false;
//     }
//   }

//   filtrerParGenre(genre: string): void {
//     this.triGenre = genre;
//     this.dropdownGenreOuvert = false;
//     this.appliquerFiltres();
//   }

//   trierParPopularite(ordre: 'asc' | 'desc'): void {
//     this.triPopularite = ordre;
//     this.dropdownPopOuvert = false;
//     this.appliquerFiltres();
//   }

//   appliquerFiltres(): void {
//     // Le genre n'existe pas encore dans l'API, on filtre juste côté client pour l'instant
//     this.mangasFiltres = [...this.tousLesMangas];
//   }

//   reinitialiser(): void {
//     this.triGenre = '';
//     this.triPopularite = '';
//     this.mangasFiltres = [...this.tousLesMangas];
//   }
 }