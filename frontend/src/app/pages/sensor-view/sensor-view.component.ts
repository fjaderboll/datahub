import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { ConfirmDialogComponent } from 'src/app/dialogs/confirm-dialog/confirm-dialog.component';
import { CreateSensorDialogComponent } from 'src/app/dialogs/create-sensor-dialog/create-sensor-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-sensor-view',
	templateUrl: './sensor-view.component.html',
	styleUrls: ['./sensor-view.component.css']
})
export class SensorViewComponent implements OnInit, AfterViewInit {
	public nodeName: string;
	public sensorName: string;
	public sensor: any;


	public displayedColumns: string[] = ['timestamp', 'value', 'actions'];
	public dataSource = new MatTableDataSource<any>();
	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

	constructor(
		public auth: AuthenticationService,
		public utils: UtilsService,
		private server: ServerService,
		private route: ActivatedRoute,
		private dialog: MatDialog,
		private router: Router
	) { }

	ngOnInit(): void {
		this.nodeName = this.route.snapshot.paramMap.get('nodeName') || 'this should never happen';
		this.sensorName = this.route.snapshot.paramMap.get('sensorName') || 'this should never happen';

		this.loadSensor();
		this.loadReadings();
	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadSensor() {
		this.server.getSensor(this.nodeName, this.sensorName).subscribe({
			next: (sensor: any) => {
				this.sensor = sensor;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	private loadReadings() {
		this.server.getReadings(this.nodeName, this.sensorName).subscribe({
			next: (readings: any) => {
				this.dataSource.data = readings;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public changedValue(property: string, newValue: any) {
		this.server.updateSensor(this.nodeName, this.sensorName, property, newValue).subscribe({
			next: (response: any) => {
				this.sensor[property] = newValue;
				if(property == "name") {
					this.sensorName = newValue;
					this.router.navigate(['/nodes/' + this.nodeName + '/sensors/' + this.sensorName]);
				}
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public deleteSensor() {
		const dialog = this.dialog.open(ConfirmDialogComponent, {
			data: {
				title: "Delete Sensor",
				text: "This will remove this sensor and all its " + this.sensor.readingCount + " readings. Are you sure?",
				action: new Observable(
					observer => {
						this.server.deleteSensor(this.nodeName, this.sensorName).subscribe({
							next: (v: any) => {
								observer.next(v);
							},
							error: (e) => {
								observer.error(e);
							},
							complete: () => {
								observer.complete();
							}
						});
					}
				)
			}
		});
		dialog.afterClosed().subscribe(confirmed => {
			if(confirmed) {
				this.router.navigate(['/nodes/' + this.nodeName]);
			}
		});
	}

	public deleteReading(reading: any) {
		this.server.deleteReading(this.nodeName, this.sensorName, reading.id).subscribe({
			next: (response: any) => {
				this.loadSensor();
				this.loadReadings();
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

}
