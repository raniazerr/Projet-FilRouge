import { Routes } from '@angular/router';
import { HomeComponent } from './components/home/home';
import { MangaDetailComponent } from './components/detail-manga/detail-manga';
import { LoginComponent } from './components/login/login';
import { InscriptionComponent } from './components/register/register';
import { PanierComponent } from './components/panier/panier';
import { ProfileComponent } from './components/profile/profile';
import { authGuard } from './auth-guard-guard';
import { adminGuard } from './admin-guard-guard';
import { AdminReservationsComponent } from './components/admin-reservations/admin-reservations';
import { AdminCatalogueComponent } from './components/admin-catalogue/admin-catalogue';
import { MentionsLegalesComponent } from './components/mentions-legales/mentions-legales';
import { PolitiqueConfidentialiteComponent } from './components/politique-confidentialite/politique-confidentialite';

export const routes: Routes = [
    { path: '', component: HomeComponent },
    { path: 'manga/:id', component: MangaDetailComponent },
    { path: 'login', component: LoginComponent },
    { path: 'register', component: InscriptionComponent },
    { path: 'panier', component: PanierComponent, canActivate: [authGuard] },
    { path: 'profile', component: ProfileComponent, canActivate: [authGuard] },
    { path: 'admin/reservations', component: AdminReservationsComponent, canActivate: [adminGuard] },
    { path: 'admin/catalogue', component: AdminCatalogueComponent, canActivate: [adminGuard] },
    { path: 'mentions-legales', component: MentionsLegalesComponent },
    { path: 'politique-confidentialite', component: PolitiqueConfidentialiteComponent },
];