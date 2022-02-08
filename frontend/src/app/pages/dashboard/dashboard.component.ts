import { AfterViewInit, Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
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
	public nodes: any;
	public sensorCount: number;
	
	public autoReload = true;
	private nextReloadTime = new Date();
	private reloadCounter = 1;
	private reloadTimer: any;
	private updateTimer: any;
	public secondsLeft = 0;

	public displayedColumns: string[] = ['nodeName', 'sensorName', 'timestamp', 'value'];
	public dataSource = new MatTableDataSource<any>();
	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

	constructor(
		private server: ServerService,
		public utils: UtilsService,
		private dialog: MatDialog
	) { }

	ngOnInit(): void {
		this.swaggerUrl = environment.apiUrl;
		this.loadNodes();

		this.updateTimer = setInterval(() => {
			this.secondsLeft = Math.ceil(this.getTimeLeft() / 1000);
			if(!this.autoReload && this.reloadTimer) {
				clearTimeout(this.reloadTimer);
				this.reloadTimer = null;
			} else if(this.autoReload && !this.reloadTimer) {
				this.nextReloadTime = new Date();
				this.reloadCounter = 1;
				this.startReadingsTimer();
			}
		}, 250);
	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	ngOnDestroy(): void {
		if(this.reloadTimer) {
			clearTimeout(this.reloadTimer);
		}
		if(this.updateTimer) {
			clearInterval(this.updateTimer);
		}
	}

	private loadNodes() {
		this.server.getNodes().subscribe({
			next: (nodes: any) => {
				this.nodes = nodes;
				this.sensorCount = 0;
				nodes.forEach((node: any) => {
					this.sensorCount += node.sensorCount;
				});
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	private loadReadings() {
		this.server.getReadings(10).subscribe({
			next: (readings: any) => {
				this.dataSource.data = readings;
				this.startReadingsTimer();
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	private startReadingsTimer() {
		this.reloadTimer = setTimeout(() => {
			if(this.getTimeLeft() <= 0) {
				this.reloadCounter++;
				this.nextReloadTime = new Date(new Date().getTime() + this.getNextReloadDelay())
				this.loadReadings();
			}
		}, this.getTimeLeft());
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
				this.loadNodes();
			}
		});
	}

}
