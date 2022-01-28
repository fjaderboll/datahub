import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { CreateUserDialogComponent } from 'src/app/dialogs/create-user-dialog/create-user-dialog.component';
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
	public createFirstUserRequired = false;
	public createUserAllowed = false;

	constructor(
		private router: Router,
		private dialog: MatDialog,
		public auth: AuthenticationService,
		private server: ServerService,
		private utils: UtilsService
	) { }

	ngOnInit(): void {
		this.loadState();
	}

	public loadState() {
		this.server.getState().subscribe({
			next: (v: any) => {
				this.createFirstUserRequired = v.createFirstUserRequired;
				this.createUserAllowed = v.createUserAllowed;
			},
			error: (e) => {
				console.log(e);
			}
		});
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

	public register() {
		const dialog = this.dialog.open(CreateUserDialogComponent);
		dialog.afterClosed().subscribe(newUsername => {
			if(newUsername) {
				this.username = newUsername;
				this.password = "";
				this.loadState();
			}
		});
	}

}
