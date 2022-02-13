import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { ConfirmDialogComponent } from 'src/app/dialogs/confirm-dialog/confirm-dialog.component';
import { CreateSensorDialogComponent } from 'src/app/dialogs/create-sensor-dialog/create-sensor-dialog.component';
import { VisualizeReadingDialogComponent } from 'src/app/dialogs/visualize-reading-dialog/visualize-reading-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-node-view',
	templateUrl: './node-view.component.html',
	styleUrls: ['./node-view.component.css']
})
export class NodeViewComponent implements OnInit, AfterViewInit {
	public node: any;
	public totalReadingCount = 0;

	public displayedColumns: string[] = ['name', 'readingCount', 'lastReadingTimestamp', 'desc'];
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
		this.loadNode();
	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadNode() {
		let name = this.route.snapshot.paramMap.get('name') || 'this should never happen';

		this.server.getNode(name).subscribe({
			next: (node: any) => {
				this.node = node;
				this.dataSource.data = node.sensors;

				this.totalReadingCount = 0;
				node.sensors.forEach((sensor: any) => {
					this.totalReadingCount += sensor.readingCount;
				});
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public changedValue(property: string, newValue: any) {
		this.server.updateNode(this.node.name, property, newValue).subscribe({
			next: (response: any) => {
				this.node[property] = newValue;
				if(property == "name") {
					this.router.navigate(['/nodes/' + this.node.name]);
				}
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public deleteNode() {
		let totalReadingCount = 0;
		this.node.sensors.forEach((sensor: any) => {
			totalReadingCount += sensor.readingCount;
		});

		const dialog = this.dialog.open(ConfirmDialogComponent, {
			data: {
				title: "Delete Node",
				text: "This will remove this node and all its " + this.node.sensors.length + " sensors and all their " + totalReadingCount + " readings. Are you sure?",
				action: new Observable(
					observer => {
						this.server.deleteNode(this.node.name).subscribe({
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
				this.router.navigate(['/nodes']);
			}
		});
	}

	public createSensor() {
		const dialog = this.dialog.open(CreateSensorDialogComponent, {
			data: {
				nodeName: this.node.name
			}
		});
		dialog.afterClosed().subscribe(newSensorName => {
			if(newSensorName) {
				this.loadNode();
			}
		});
	}

	public visualizeReadings() {
		this.dialog.open(VisualizeReadingDialogComponent, {
			data: {
				nodeName: this.node.name,
				sensorName: null,
				readings: null
			}
		});
	}
	
}
