import { Injectable } from '@angular/core';

@Injectable({
	providedIn: 'root'
})
export class AuthenticationService {
	private loggedIn: boolean = false;

	constructor() { }

	public isLoggedIn() {
		return this.loggedIn;
	}

	public login(username: string, password: string) {
		this.loggedIn = true;
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
