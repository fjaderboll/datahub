import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-create-sensor-dialog',
	templateUrl: './create-sensor-dialog.component.html',
	styleUrls: ['./create-sensor-dialog.component.css']
})
export class CreateSensorDialogComponent implements OnInit {
	public name: string = "";
	public description: string = "";
	public inputData: any;

	constructor(
		public dialogRef: MatDialogRef<CreateSensorDialogComponent>,
		private server: ServerService,
		private utils: UtilsService,
		@Inject(MAT_DIALOG_DATA) public data: any
	) {
		this.inputData = data;
	}


	ngOnInit(): void {
	}

	public isFormValid() {
		return this.name.length > 0;
	}

	public create() {
		this.server.createSensor(this.inputData.nodeName, this.name, this.description).subscribe({
			next: (v: any) => {
				this.utils.toastSuccess(v);
				this.dialogRef.close(this.name);
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}
}
