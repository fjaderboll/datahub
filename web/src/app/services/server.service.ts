import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { UtilsService } from './utils.service';

@Injectable({
	providedIn: 'root'
})
export class ServerService {

	constructor(
		private http: HttpClient,
		private utils: UtilsService
	) { }

	public showHttpError(error: any) {
		this.utils.toastError(error.message);
	}

	public login(username: string, password: string) {
		let baseUrl = "datahub2/api/rest";
		let url = baseUrl + "/users/" + username + "/login";
		return this.http.post(url, { password });
	}
}
