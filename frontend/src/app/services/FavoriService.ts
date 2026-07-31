import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Favori {
  id: number;
  utilisateur: number;
  manga: number;
}

@Injectable({ providedIn: 'root' })
export class FavoriService {
  // Service de gestion des favoris utilisateur.
  // Il centralise les requêtes pour ajouter, lister et supprimer des favoris.
  private apiUrl = 'http://localhost:8000/api/favori';

  constructor(private http: HttpClient) {}

  getFavoris(): Observable<Favori[]> {
    return this.http.get<Favori[]>(this.apiUrl);
  }

  getMesFavoris(): Observable<any[]> {
  return this.http.get<any[]>(`${this.apiUrl}/mes-favoris`);
}

  ajouter(utilisateurId: number, mangaId: number): Observable<Favori> {
    return this.http.post<Favori>(`${this.apiUrl}/new`, { utilisateur: utilisateurId, manga: mangaId });
  }

  supprimer(favoriId: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${favoriId}`);
  }
}