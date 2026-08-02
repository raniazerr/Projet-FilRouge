import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Manga {
  id: number;
  api_id: number;
  titre: string;
  image: string;
  synopsis?: string;
  genres?: string[];
}

export interface MangaResponse {
  mangas: Manga[];
  top: Manga[];
}

export interface MangaApiResult {
  api_id: number;
  titre: string;
  image: string;
  synopsis?: string;
  volumes?: number | null;
  statut?: string | null;
}

export interface Tome {
  id: number;
  numero_tome: number;
  stock: number;
  prix: number;
  manga_id: number;
  manga_titre: string;
}

export interface TomePayload {
  manga_id: number;
  numero_tome: number;
  stock: number;
  prix: number;
}

@Injectable({
  providedIn: 'root',
})
export class ServManga {
  // Service central pour la gestion des mangas côté front-end.
  // Il centralise les appels HTTP vers l'API Symfony pour afficher,
  // rechercher, ajouter ou gérer les tomes d'un manga.
  private apiUrl = 'http://localhost:8000';

  constructor(private http: HttpClient) {}

 getMangas(tri?: string): Observable<MangaResponse> {
    let params = new HttpParams();
    if (tri) {
      params = params.set('tri', tri);
    }
    return this.http.get<MangaResponse>(`${this.apiUrl}/manga/index`, { params });
  }

  getMangaById(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/manga/${id}`);
  }

  // --- Admin : recherche externe + ajout au catalogue ---

  searchMangaApi(query: string): Observable<MangaApiResult[]> {
    return this.http.get<MangaApiResult[]>(`${this.apiUrl}/manga/search`, {
      params: { q: query },
    });
  }

  ajouterMangaAuCatalogue(apiId: number): Observable<Manga> {
    return this.http.post<Manga>(`${this.apiUrl}/manga/new`, { api_id: apiId });
  }

  // --- Admin : gestion des tomes ---

  getTomesByManga(mangaId: number): Observable<Tome[]> {
    return this.http.get<Tome[]>(`${this.apiUrl}/tome/manga/${mangaId}`);
  }

  creerTome(payload: TomePayload): Observable<Tome> {
    return this.http.post<Tome>(`${this.apiUrl}/tome`, payload);
  }

  modifierTome(id: number, data: { prix: number; stock: number }): Observable<Tome> {
  return this.http.patch<Tome>(`${this.apiUrl}/tome/${id}`, data);
}

  supprimerTome(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/tome/${id}`);
  }
}