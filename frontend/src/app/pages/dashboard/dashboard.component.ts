import { AfterViewInit, Component, OnDestroy, OnInit, QueryList, ViewChild, ViewChildren } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
import { CreateSensorDialogComponent } from 'src/app/dialogs/create-sensor-dialog/create-sensor-dialog.component';
import { CreateTokenDialogComponent } from 'src/app/dialogs/create-token-dialog/create-token-dialog.component';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit, AfterViewInit, OnDestroy {
	public swaggerUrl: string;
	public totalReadingCount: number;
	public overview: any;
	
	public autoReload = true;
	private nextReloadTime = new Date();
	private reloadCounter = 1;
	private reloadTimer: any;
	private updateTimer: any;
	public secondsLeft = 0;

	public displayedColumns1: string[] = ['nodeName', 'sensorName', 'timestamp', 'value'];
	public displayedColumns2: string[] = ['nodeName', 'name', 'readingCount', 'lastReadingTimestamp', 'lastReadingValue'];
	public dataSource1 = new MatTableDataSource<any>();
	public dataSource2 = new MatTableDataSource<any>();
	@ViewChildren(MatPaginator) paginators = new QueryList<MatPaginator>();
	@ViewChildren(MatSort) sorts = new QueryList<MatSort>();

	constructor(
		private server: ServerService,
		public utils: UtilsService,
		private dialog: MatDialog
	) { }

	ngOnInit(): void {
		this.swaggerUrl = environment.apiUrl;

		this.updateTimer = setInterval(() => {
			this.secondsLeft = Math.ceil(this.getTimeLeft() / 1000);
			if(!this.autoReload && this.reloadTimer) {
				this.stopTimer();
			} else if(this.autoReload && !this.reloadTimer) {
				this.startTimer();
			}
		}, 250);
	}

	ngAfterViewInit() {
		this.dataSource1.paginator = this.paginators.toArray()[0];
		this.dataSource1.sort = this.sorts.toArray()[0];
		this.dataSource2.paginator = this.paginators.toArray()[1];
		this.dataSource2.sort = this.sorts.toArray()[1];
	}

	ngOnDestroy(): void {
		if(this.reloadTimer) {
			clearTimeout(this.reloadTimer);
		}
		if(this.updateTimer) {
			clearInterval(this.updateTimer);
		}
	}

	private loadOverview() {
		this.server.getOverview().subscribe({
			next: (overview: any) => {
				if(this.overview) {
					overview.lastReadings.forEach((nr: any) => {
						nr.new = !this.overview.lastReadings.some((r: any) => {
							return nr.id == r.id;
						});
					});

					overview.sensors.forEach((sensor: any) => {
						sensor.new = !this.overview.sensors.some((s: any) => {
							return sensor.nodeName == s.nodeName && sensor.name == s.name;
						});
						sensor.newReading = this.overview.sensors.some((s: any) => {
							return sensor.nodeName == s.nodeName && sensor.name == s.name && sensor.lastReading.id != s.lastReading.id;
						});
					});
				}

				this.totalReadingCount = 0;
				overview.sensors.forEach((sensor: any) => {
					this.totalReadingCount += sensor.readingCount;
					sensor.lastReadingTimestamp = sensor.lastReading?.timestamp;
				});

				this.dataSource1.data = overview.lastReadings;
				this.dataSource2.data = overview.sensors;
				this.overview = overview;
				this.startTimer();
			},
			error: (e) => {
				this.server.showHttpError(e);
				this.autoReload = false;
			}
		});
	}

	private startTimer() {
		this.reloadTimer = setTimeout(() => {
			if(this.getTimeLeft() <= 0) {
				this.reloadCounter++;
				this.nextReloadTime = new Date(new Date().getTime() + this.getNextReloadDelay())
				this.loadOverview();
			}
		}, this.getTimeLeft());
	}

	private stopTimer() {
		if(this.reloadTimer) {
			clearTimeout(this.reloadTimer);
		}
		this.reloadTimer = null;
		this.nextReloadTime = new Date();
		this.reloadCounter = 1;
	}

	private forceReload() {
		this.stopTimer();
		this.loadOverview();
	}

	private getTimeLeft() {
		let t = this.nextReloadTime.getTime() - new Date().getTime();
		return Math.max(0, t);
	}

	private getNextReloadDelay() {
		return Math.floor(Math.log(this.reloadCounter) / Math.log(2) * 5 * 1000);
	}

	public createNode() {
		const dialog = this.dialog.open(CreateNodeDialogComponent);
		dialog.afterClosed().subscribe(newName => {
			if(newName) {
				this.forceReload();
			}
		});
	}

	public createSensor() {
		const dialog = this.dialog.open(CreateSensorDialogComponent, {
			data: {
				nodeName: this.overview.nodes[0].name
			}
		});
		dialog.afterClosed().subscribe(newSensorName => {
			if(newSensorName) {
				this.forceReload();
			}
		});
	}

	public createToken() {
		const dialog = this.dialog.open(CreateTokenDialogComponent);
		dialog.afterClosed().subscribe(created => {
			if(created) {
				this.forceReload();
			}
		});
	}

}
