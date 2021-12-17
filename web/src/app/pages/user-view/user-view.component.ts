import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ServerService } from 'src/app/services/server.service';

@Component({
  selector: 'app-user-view',
  templateUrl: './user-view.component.html',
  styleUrls: ['./user-view.component.css']
})
export class UserViewComponent implements OnInit {
	public user: any;

	constructor(
		private server: ServerService,
		private route: ActivatedRoute
	) { }

	ngOnInit(): void {
		this.loadUser();
	}

	private loadUser() {
		let username = this.route.snapshot.paramMap.get('username') || 'this should never happen';

		this.server.getUser(username).subscribe({
			next: (user: any) => {
				//user.datasetsSizeStr = this.utils.printFilesize(user.datasetsSize);
				this.user = user;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public changedValue(property: string, newValue: any) {
		this.server.updateUser(this.user.username, property, newValue).subscribe({
			next: (response: any) => {
				this.user[property] = newValue;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public impersonate() {
		
	}

}
