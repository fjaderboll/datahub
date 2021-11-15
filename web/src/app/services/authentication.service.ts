import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { ServerService } from './server.service';

@Injectable({
	providedIn: 'root'
})
export class AuthenticationService {
	private token: string | null = null;
	private username: string | null = null;
	private admin: boolean = false;

	constructor(
		public server: ServerService
	) {
		this.setToken(
			localStorage.getItem('token'),
			localStorage.getItem('username'),
			localStorage.getItem('admin') == "true"
		);
	}

	public login(username: string, password: string) {
		return new Observable(
			observer => {
				this.server.login(username, password).subscribe({
					next: (v: any) => {
						this.setToken(v.token, v.username, v.admin);
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

	public setToken(token: string | null, username: string | null, admin: boolean) {
		if(token && token.length > 0 && username && username.length > 0) {
			this.token = token;
			this.username = username;
			this.admin = admin;
			localStorage.setItem('token', this.token);
			localStorage.setItem('username', this.username);
			localStorage.setItem('admin', this.admin + "");
		} else {
			this.token = null;
			localStorage.removeItem('token');
		}
	}

	public getToken() {
		return this.token;
	}

	public isLoggedIn() {
		return !!this.token;
	}

	public logout() {
		this.setToken(null, this.username, this.admin);
	}

	public isAdmin() {
		return this.isLoggedIn() && this.admin;
	}

	public getUsername() {
		return this.username;
	}

}
