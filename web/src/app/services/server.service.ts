import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { UtilsService } from './utils.service';
import { environment } from './../../environments/environment';

@Injectable({
	providedIn: 'root'
})
export class ServerService {
	private apiUrl: string;

	constructor(
		private http: HttpClient,
		private utils: UtilsService
	) {
		this.apiUrl = environment.apiUrl;
	}

	public showHttpError(error: any) {
		this.utils.toastError(error.message);
	}

	public login(username: string, password: string) {
		let url = this.apiUrl + "users/" + username + "/login";
		return this.http.post(url, { password });
	}
}
