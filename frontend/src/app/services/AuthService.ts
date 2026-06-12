import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuthService {

  private apiUrl = 'http://localhost:8000/api';
  private connecteSubject = new BehaviorSubject<boolean>(!!localStorage.getItem('token'));
  connecte$ = this.connecteSubject.asObservable();

  constructor(private http: HttpClient) {}

  register(data: { email: string; password: string; nom: string; prenom: string }): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, data);
  }

  login(email: string, password: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, { email, password }).pipe(
      tap((res: any) => {
        localStorage.setItem('token', res.token);
        this.connecteSubject.next(true);
      })
    );
  }

  logout(): void {
    localStorage.removeItem('token');
    this.connecteSubject.next(false);
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  isConnecte(): boolean {
    return this.connecteSubject.value;
  }
}