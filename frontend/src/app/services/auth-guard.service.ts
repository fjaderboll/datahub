import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { AuthenticationService } from './authentication.service';
import { UtilsService } from './utils.service';

@Injectable({
	providedIn: 'root'
})
export class AuthGuardService implements CanActivate {

	constructor(
		private router: Router,
		private auth: AuthenticationService,
		private utils: UtilsService
  	) { }

	canActivate(
		route: ActivatedRouteSnapshot,
		state: RouterStateSnapshot
	): boolean {
		if(!this.auth.isLoggedIn()) {
			this.utils.toastError("Unauthorized, please login");
			this.router.navigate(["login"]);
			return false;
		}
		return true;
	}
}
