import { Component, OnInit } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-create-token-dialog',
	templateUrl: './create-token-dialog.component.html',
	styleUrls: ['./create-token-dialog.component.css']
})
export class CreateTokenDialogComponent implements OnInit {
	public description: string = "";

	constructor(
		public dialogRef: MatDialogRef<CreateTokenDialogComponent>,
		private server: ServerService,
		private utils: UtilsService
	) { }

	ngOnInit(): void {
	}

  	public create() {
		this.server.createToken(true, true, true, this.description).subscribe({
			next: (v: any) => {
				this.utils.toastSuccess(v);
				this.dialogRef.close(true);
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

}
