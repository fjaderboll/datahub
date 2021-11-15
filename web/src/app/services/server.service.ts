import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { UtilsService } from './utils.service';
import { environment } from './../../environments/environment';
import { AuthenticationService } from './authentication.service';

@Injectable({
	providedIn: 'root'
})
export class ServerService {
	private apiUrl: string;
	private httpOptions: any;

	constructor(
		private http: HttpClient,
		private utils: UtilsService
	) {
		this.apiUrl = environment.apiUrl;
	}

	public setToken(token: string) {
		this.httpOptions = {
			headers: new HttpHeaders({
				'Content-Type': 'application/json',
				'Authorization': ' Bearer ' + token
			})
		};
	}

	public showHttpError(error: any) {
		this.utils.toastError(error.message);
	}

	public login(username: string, password: string) {
		let url = this.apiUrl + "users/" + username + "/login";
		return this.http.post(url, { password });
	}

	public getUsers() {
		let url = this.apiUrl + "users/";
		return this.http.get(url, this.httpOptions);
	}

}
