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
		if(error.error && (typeof error.error === 'string' || error.error instanceof String)) {
			this.utils.toastError(error.error);
		} else {
			this.utils.toastError(error.message);
		}
	}

	public getState() {
		const url = this.apiUrl + "state/";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getOverview() {
		const url = this.apiUrl + "overview/";
		return this.http.get(url, this.httpOptionsJson);
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

	public deleteUser(username: string) {
		const url = this.apiUrl + "users/" + username;
		return this.http.delete(url, this.httpOptionsText);
	}

	public createNode(name: string, desc: string) {
		const url = this.apiUrl + "nodes";
		return this.http.post(url, { name, desc }, this.httpOptionsText);
	}

	public getNodes() {
		const url = this.apiUrl + "nodes";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getNode(name: string) {
		const url = this.apiUrl + "nodes/" + name;
		return this.http.get(url, this.httpOptionsJson);
	}

	public updateNode(name: string, property: string, value: any) {
		const url = this.apiUrl + "nodes/" + name;
		return this.http.put(url, { [property]: value }, this.httpOptionsText);
	}

	public deleteNode(name: string) {
		const url = this.apiUrl + "nodes/" + name;
		return this.http.delete(url, this.httpOptionsText);
	}

	public createToken(enabled: boolean, read: boolean, write: boolean, desc: string) {
		const url = this.apiUrl + "tokens/";
		return this.http.post(url, { enabled, read, write, desc }, this.httpOptionsText);
	}

	public getTokens() {
		const url = this.apiUrl + "tokens/";
		return this.http.get(url, this.httpOptionsJson);
	}

	public updateToken(id: number, property: string, value: any) {
		const url = this.apiUrl + "tokens/" + id;
		return this.http.put(url, { [property]: value }, this.httpOptionsText);
	}

	public deleteToken(id: number) {
		const url = this.apiUrl + "tokens/" + id;
		return this.http.delete(url, this.httpOptionsText);
	}

	public createExport(enabled: boolean, protocol: string, format: string, url: string, auth1: string, auth2: string) {
		const url0 = this.apiUrl + "exports/";
		return this.http.post(url0, { enabled, protocol, format, url, auth1, auth2 }, this.httpOptionsText);
	}

	public getExports() {
		const url = this.apiUrl + "exports/";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getExportProtocols() {
		const url = this.apiUrl + "exports/protocols";
		return this.http.get(url, this.httpOptionsJson);
	}

	public getExportFormats() {
		const url = this.apiUrl + "exports/formats";
		return this.http.get(url, this.httpOptionsJson);
	}

	public updateExport(id: number, property: string, value: any) {
		const url = this.apiUrl + "exports/" + id;
		return this.http.put(url, { [property]: value }, this.httpOptionsText);
	}

	public deleteExport(id: number) {
		const url = this.apiUrl + "exports/" + id;
		return this.http.delete(url, this.httpOptionsText);
	}

	public createSensor(nodeName: string, name: string, desc: string) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors";
		return this.http.post(url, { name, desc }, this.httpOptionsText);
	}

	public getSensor(nodeName: string, sensorName: string) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName;
		return this.http.get(url, this.httpOptionsJson);
	}

	public updateSensor(nodeName: string, sensorName: string, property: string, value: any) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName;
		return this.http.put(url, { [property]: value }, this.httpOptionsText);
	}

	public deleteSensor(nodeName: string, sensorName: string) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName;
		return this.http.delete(url, this.httpOptionsText);
	}

	public getSensorReadings(nodeName: string, sensorName: string, limit: number) {
		let url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName + "/readings";
		if(limit) {
			url += "?limit=" + limit;
		}
		return this.http.get(url, this.httpOptionsJson);
	}

	public getNodeReadings(nodeName: string, limit: number) {
		let url = this.apiUrl + "nodes/" + nodeName + "/readings";
		if(limit) {
			url += "?limit=" + limit;
		}
		return this.http.get(url, this.httpOptionsJson);
	}

	public getReadings(limit: number) {
		let url = this.apiUrl + "readings";
		if(limit) {
			url += "?limit=" + limit;
		}
		return this.http.get(url, this.httpOptionsJson);
	}

	public createReading(nodeName: string, sensorName: string, value: string, offset: number) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName + "/readings?offset=" + offset;
		return this.http.post(url, { value }, this.httpOptionsText);
	}

	public exportReading(nodeName: string, sensorName: string, readingId: number) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName + "/readings/" + readingId + "/export";
		return this.http.put(url, {}, this.httpOptionsText);
	}

	public deleteReading(nodeName: string, sensorName: string, readingId: number) {
		const url = this.apiUrl + "nodes/" + nodeName + "/sensors/" + sensorName + "/readings/" + readingId;
		return this.http.delete(url, this.httpOptionsText);
	}

}
