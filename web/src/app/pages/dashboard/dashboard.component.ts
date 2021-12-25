import { Component, OnInit } from '@angular/core';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';
import { environment } from './../../../environments/environment';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
	public swaggerUrl: string;
	public user: any;
	public totalDatasetSize: string;

	constructor(
		private auth: AuthenticationService,
		private server: ServerService,
		private utils: UtilsService
	) { }

	ngOnInit(): void {
		this.swaggerUrl = environment.apiUrl;
		this.loadUser();
	}

	private loadUser() {
		this.server.getUser(this.auth.getUsername() + "").subscribe({
			next: (user: any) => {
				this.user = user;

				let size = 0;
				this.user.datasets.forEach((dataset: any) => {
					size += dataset.size;
				});
				this.totalDatasetSize = this.utils.printFilesize(size);
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

}
