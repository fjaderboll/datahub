import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { ServerService } from './server.service';

@Injectable({
	providedIn: 'root'
})
export class AuthenticationService {
	private loggedIn: boolean = true;

	constructor(
		public server: ServerService
	) { }

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

	public isLoggedIn() {
		let token = localStorage.getItem('token');
		if(token) {
			return true;
		}
		return false;
	}

	public logout() {
		localStorage.removeItem('token');
	}

	public isAdmin() {
		return this.isLoggedIn() && true;
	}

	public getUsername() {
		return "laban";
	}

}
