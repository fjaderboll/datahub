import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { UtilsService } from './utils.service';
import { environment } from '../../environments/environment';

@Injectable({
	providedIn: 'root'
})
export class ServerService {
	private apiUrl: string;
	private httpOptionsJson: any;
	private httpOptionsText: any;

	constructor(
		private http: HttpClient,
		private utils: UtilsService
	) {
		this.apiUrl = environment.apiUrl;
	}

	public setToken(token: string) {
		let headers = new HttpHeaders({
			'Content-Type': 'application/json',
			'Authorization': ' Bearer ' + token
		});
		this.httpOptionsJson = {
			headers,
			responseType: 'json'
		};
		this.httpOptionsText = {
			headers,
			responseType: 'text'
		};
	}

	public showHttpError(error: any) {
		console.log(error);
		this.utils.toastError(error.message);
	}

	public login(username: string, password: string) {
		const url = this.apiUrl + "users/" + username + "/login";
		return this.http.post(url, { password });
	}

	public impersonate(username: string) {
		const url = this.apiUrl + "users/" + username + "/impersonate";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getUsers() {
		const url = this.apiUrl + "users/";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getUser(username: string) {
		const url = this.apiUrl + "users/" + username;
		return this.http.get(url, this.httpOptionsJson);
	}

	public updateUser(username: string, property: string, value: any) {
		const url = this.apiUrl + "users/" + username;
		return this.http.put(url, { [property]: value }, this.httpOptionsText);
	}

	public createUser(username: string, password: string) {
		const url = this.apiUrl + "users/";
		return this.http.post(url, { username, password }, this.httpOptionsText);
	}

	public getDatasets() {
		const url = this.apiUrl + "datasets/";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getDataset(name: string) {
		const url = this.apiUrl + "datasets/" + name;
		return this.http.get(url, this.httpOptionsJson);
	}

	public updateDataset(name: string, property: string, value: any) {
		const url = this.apiUrl + "datasets/" + name;
		return this.http.put(url, { [property]: value }, this.httpOptionsText);
	}

	public createDataset(name: string, desc: string) {
		const url = this.apiUrl + "datasets/";
		return this.http.post(url, { name, desc }, this.httpOptionsText);
	}

	public createNode(datasetName: string, nodeName: string, nodeDesc: string) {
		const url = this.apiUrl + "datasets/" + datasetName + "/nodes";
		return this.http.post(url, { name: nodeName, desc: nodeDesc }, this.httpOptionsText);
	}

	public getDatasetNodes(name: string) {
		const url = this.apiUrl + "datasets/" + name + "/nodes";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getDatasetTokens(name: string) {
		const url = this.apiUrl + "datasets/" + name + "/tokens";
		return this.http.get(url, this.httpOptionsJson);
	}

}
