import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
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

@Injectable({
  providedIn: 'root',
})
export class ServManga {
  private apiUrl = 'http://127.0.0.1:8000';

  constructor(private http: HttpClient) {}

  getMangas(): Observable<MangaResponse> {
    return this.http.get<MangaResponse>(`${this.apiUrl}/manga/index`);
  }

  getMangaById(id: number): Observable<any> {
  return this.http.get(`${this.apiUrl}/manga/${id}`);
}
}