import { Routes } from '@angular/router';
import { HomeComponent } from './components/home/home';
import { MangaDetailComponent } from './components/detail-manga/detail-manga';
import { LoginComponent } from './components/login/login';
import { InscriptionComponent } from './components/register/register';
import { PanierComponent } from './components/panier/panier';
import { ProfileComponent } from './components/profile/profile';

export const routes: Routes = [
    { path: '', component: HomeComponent },
    { path: 'manga/:id', component: MangaDetailComponent },
    { path: 'login', component: LoginComponent },
    { path: 'register', component: InscriptionComponent },
    { path: 'panier', component: PanierComponent },
    { path: 'profile', component: ProfileComponent },
];
