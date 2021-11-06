import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthenticationService } from 'src/app/services/authentication.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
	public showPassword: boolean = false;

	constructor(
		private router: Router,
		public auth: AuthenticationService
	) { }

	ngOnInit(): void {
	}

	public login() {
		this.auth.login("", "").subscribe({
			next: (v) => {
				this.router.navigate(['/']);
			},
			error: (e) => {
				console.log(e);
			}
		});
	}

}
