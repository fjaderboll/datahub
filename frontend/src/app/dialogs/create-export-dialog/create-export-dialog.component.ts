import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';
import { CreateReadingDialogComponent } from '../create-reading-dialog/create-reading-dialog.component';

@Component({
  selector: 'app-create-export-dialog',
  templateUrl: './create-export-dialog.component.html',
  styleUrls: ['./create-export-dialog.component.css']
})
export class CreateExportDialogComponent implements OnInit {
	public protocols: any;
	public protocol: any;
	public formats: any;
	public format: any;
	public url: string = "";
	public auth1: string = "";
	public auth2: string = "";

	constructor(
		public dialogRef: MatDialogRef<CreateReadingDialogComponent>,
		private server: ServerService,
		private utils: UtilsService
	) {
	}

	ngOnInit(): void {
		this.loadProtocols();
		this.loadFormats();
	}

	private loadProtocols() {
		this.server.getExportProtocols().subscribe({
			next: (v: any) => {
				this.protocols = v;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	private loadFormats() {
		this.server.getExportFormats().subscribe({
			next: (v: any) => {
				this.formats = v;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public isFormValid() {
		return this.protocol && this.format && this.url.length > 0;
	}

	public create() {
		this.server.createExport(false, this.protocol.code, this.format.code, this.url, this.auth1, this.auth2).subscribe({
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
