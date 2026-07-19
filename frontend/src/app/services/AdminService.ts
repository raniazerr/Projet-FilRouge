import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface CommandeAdmin {
  id: number;
  statut: string;
  date_commande: string;
  utilisateur: {
    id: number;
    nom: string;
    prenom: string;
    email: string;
  };
  reservations: any[];
  total: number;
}

@Injectable({ providedIn: 'root' })
export class ServAdmin {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getToutesCommandes(): Observable<CommandeAdmin[]> {
    return this.http.get<CommandeAdmin[]>(`${this.apiUrl}/commandes/admin`);
  }

  updateStatutCommande(id: number, statut: 'confirmée' | 'expirée'): Observable<CommandeAdmin> {
    return this.http.patch<CommandeAdmin>(`${this.apiUrl}/commandes/admin/${id}/statut`, { statut });
  }
}