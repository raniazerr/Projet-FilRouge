import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { PanierService, ReservationItem } from '../../services/PanierService';

@Component({
  selector: 'app-panier',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './panier.html',
  styleUrl: './panier.scss'
})
export class PanierComponent implements OnInit {

  reservations: ReservationItem[] = [];
  chargement = true;
  erreur = '';

  constructor(
    private panierService: PanierService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.chargerPanier();
  }

  chargerPanier(): void {
    this.panierService.getReservations().subscribe({
      next: (data) => {
        this.reservations = data;
        this.chargement = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.erreur = 'Impossible de charger le panier.';
        this.chargement = false;
        this.cdr.detectChanges();
      }
    });
  }

  supprimer(id: number): void {
    this.panierService.supprimerReservation(id).subscribe({
      next: () => {
        this.reservations = this.reservations.filter(r => r.id !== id);
        this.cdr.detectChanges();
      }
    });
  }

  validerTout(): void {
    const actives = this.reservations.filter(r => r.statut === 'active');
    actives.forEach(r => {
      this.panierService.validerReservation(r.id).subscribe({
        next: () => {
          this.reservations = this.reservations.filter(res => res.id !== r.id);
          this.cdr.detectChanges();
        }
      });
    });
  }

  get total(): number {
    return this.reservations.reduce((sum, r) => sum + r.tome.prix, 0);
  }
}