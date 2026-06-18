import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface ReservationItem {
  id: number;
  tome: {
    id: number;
    numero_tome: number;
    prix: number;
    stock: number;
    manga_titre: string;
    manga_image: string;
  };
}

export interface Commande {
  id: number;
  statut: string;
  date_commande: string;
  utilisateur: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
  reservations: ReservationItem[];
  total: number;
}

@Injectable({ providedIn: 'root' })
export class PanierService {

  private apiUrl = 'http://localhost:8000/api/commandes';

  constructor(private http: HttpClient) {}

  getCommandes(): Observable<Commande[]> {
    return this.http.get<Commande[]>(this.apiUrl);
  }

  ajouterAuPanier(tomeId: number): Observable<Commande> {
    return this.http.post<Commande>(`${this.apiUrl}/ajouter`, { tome_id: tomeId });
  }

  supprimerReservation(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/reservation/${id}`);
  }

  annulerCommande(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }

  soumettre(id: number): Observable<any> {
  return this.http.patch(`${this.apiUrl}/${id}/soumettre`, {});
  }
}