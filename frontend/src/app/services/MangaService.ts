import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Manga {
  id: number;
  api_id: number;
  titre: string;
  image: string;
  synopsis?: string;
}

@Injectable({
  providedIn: 'root',
})
export class ServManga {
  private apiUrl = 'http://127.0.0.1:8000';

  constructor(private http: HttpClient) {}

  getMangas(): Observable<Manga[]> {
    return this.http.get<Manga[]>(`${this.apiUrl}/manga/index`);
  }

}