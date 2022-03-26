import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-create-reading-dialog',
  templateUrl: './create-reading-dialog.component.html',
styleUrls: ['./create-reading-dialog.component.css']
})
export class CreateReadingDialogComponent implements OnInit {
	public nodes: any;
	public node: any;
	public sensors: any;
	public sensor: any;
	public value: string = "";
	public offset: number = 0;
	public inputData: any;

	constructor(
		public dialogRef: MatDialogRef<CreateReadingDialogComponent>,
		private server: ServerService,
		private utils: UtilsService,
		@Inject(MAT_DIALOG_DATA) public data: any
	) {
		this.inputData = data;
	}

	ngOnInit(): void {
		this.loadNodes();
	}

	private loadNodes() {
		this.server.getNodes().subscribe({
			next: (v: any) => {
				this.nodes = v;
				if(this.inputData.nodeName) {
					this.nodes.forEach((node: any) => {
						if(node.name === this.inputData.nodeName) {
							this.node = node;
							this.loadSensors();
						}
					});
				}
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public loadSensors() {
		this.server.getNode(this.node.name).subscribe({
			next: (v: any) => {
				this.sensors = v.sensors;
				if(this.inputData.sensorName) {
					this.sensors.forEach((sensor: any) => {
						if(sensor.name === this.inputData.sensorName) {
							this.sensor = sensor;
						}
					});
				}
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public isFormValid() {
		return this.node && this.sensor && this.value.length > 0;
	}

	public create() {
		this.server.createReading(this.node.name, this.sensor.name, this.value, this.offset).subscribe({
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
