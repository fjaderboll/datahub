import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { ServerService } from './server.service';

@Injectable({
	providedIn: 'root'
})
export class AuthenticationService {
	private loggedIn: boolean = false;

	constructor(
		public server: ServerService
	) { }

	public isLoggedIn() {
		return this.loggedIn;
	}

	public login(username: string, password: string) {
		return new Observable(
			observer => {
				this.server.login(username, password).subscribe({
					next: (v) => {
						this.loggedIn = true;
						observer.next(v);
					},
					error: (e) => {
						observer.error(e);
					},
					complete: () => {
						observer.complete();
					}
				});
			}
		);
	}

	public logout() {
		this.loggedIn = false;
	}

	public isAdmin() {
		return this.isLoggedIn() && true;
	}

	public getUsername() {
		return "laban";
	}

}
