import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface ReservationItem {
  id: number;
  statut: string;
  date_reservation: string;
  tome: {
    id: number;
    numero_tome: number;
    prix: number;
    stock: number;
    manga_titre: string;
    manga_image: string;
  };
}

@Injectable({ providedIn: 'root' })
export class PanierService {

  private apiUrl = 'http://localhost:8000/api/reservations';

  constructor(private http: HttpClient) {}

  getReservations(): Observable<ReservationItem[]> {
    return this.http.get<ReservationItem[]>(this.apiUrl);
  }

  ajouterAuPanier(tomeId: number): Observable<any> {
    return this.http.post(this.apiUrl, { tome_id: tomeId });
  }

  supprimerReservation(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }

  validerReservation(id: number): Observable<any> {
    return this.http.patch(`${this.apiUrl}/${id}/valider`, {});
  }
}