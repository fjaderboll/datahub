import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
	public showPassword: boolean = false;
	public username: string = "";
	public password: string = "";

	constructor(
		private router: Router,
		public auth: AuthenticationService,
		private server: ServerService,
		private utils: UtilsService
	) { }

	ngOnInit(): void {
	}

	public login() {
		this.auth.login(this.username, this.password).subscribe({
			next: (v) => {
				this.router.navigate(['/']);
				this.utils.toastSuccess("Successfully signed in");
			},
			error: (e) => {
				if(e.status == 403) {
					this.utils.toastError("Invalid credentials");
				} else {
					this.server.showHttpError(e);
				}
			}
		});
	}

}
