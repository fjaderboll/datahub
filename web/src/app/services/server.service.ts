import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
	providedIn: 'root'
})
export class ServerService {

	constructor(
		private http: HttpClient
	) { }

	public login(username: string, password: string) {
		let baseUrl = "datahub2/api/rest";
		let url = baseUrl + "/users/" + username + "/login";
		return this.http.post(url, { password });
	}
}
