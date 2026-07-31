import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface UserProfile {
  id: number;
  email: string;
  nom: string;
  prenom: string;
  date_inscription: string;
  roles: string[];
}

export interface UpdateProfilePayload {
  nom?: string;
  prenom?: string;
  email?: string;
  password?: string;
  currentPassword?: string;
}

@Injectable({ providedIn: 'root' })
export class ServUser {
  // Service dédié à la gestion du profil utilisateur.
  // Il permet de récupérer les informations du compte et de les modifier.
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getProfile(): Observable<UserProfile> {
    return this.http.get<UserProfile>(`${this.apiUrl}/profile`);
  }

  updateProfile(payload: UpdateProfilePayload): Observable<UserProfile> {
    return this.http.patch<UserProfile>(`${this.apiUrl}/profile`, payload);
  }

  getHistoriqueCommandes(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/commandes/historique`);
  }
}