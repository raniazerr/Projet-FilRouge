import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { ServUser } from './services/UserService';
import { map, catchError, of } from 'rxjs';

export const adminGuard: CanActivateFn = () => {
  const servUser = inject(ServUser);
  const router = inject(Router);

  return servUser.getProfile().pipe(
    map((user) => {
      if (user.roles?.includes('ROLE_ADMIN')) {
        return true;
      }
      router.navigate(['/']);
      return false;
    }),
    catchError(() => {
      router.navigate(['/login']);
      return of(false);
    })
  );
};