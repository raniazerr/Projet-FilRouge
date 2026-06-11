import { Routes } from '@angular/router';
import { HomeComponent } from './components/home/home';
import { MangaDetailComponent } from './components/detail-manga/detail-manga';
import { LoginComponent } from './components/login/login';

export const routes: Routes = [
    { path: '', component: HomeComponent },
    { path: 'manga/:id', component: MangaDetailComponent },
    { path: 'login', component: LoginComponent }
];
