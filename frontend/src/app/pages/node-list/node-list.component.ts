import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
import { VisualizeReadingDialogComponent } from 'src/app/dialogs/visualize-reading-dialog/visualize-reading-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-node-list',
  templateUrl: './node-list.component.html',
  styleUrls: ['./node-list.component.css']
})
export class NodeListComponent implements OnInit, AfterViewInit {
	public displayedColumns: string[] = ['name', 'sensorCount', 'readingCount', 'lastReadingTimestamp', 'desc'];
	public dataSource = new MatTableDataSource<any>();
	public totalReadingCount = 0;

	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

  	constructor(
		public auth: AuthenticationService,
		private server: ServerService,
		public utils: UtilsService,
		private dialog: MatDialog
	) { }

  	ngOnInit(): void {
		this.loadNodes();
  	}
	
	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadNodes() {
		this.server.getNodes().subscribe({
			next: (v: any) => {
				this.totalReadingCount = 0;
				v.forEach((node: any) => {
					this.totalReadingCount += node.readingCount;
					node.lastReadingTimestamp = node.lastReading?.timestamp;
				});
				this.dataSource.data = v;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public createNode() {
		const dialog = this.dialog.open(CreateNodeDialogComponent);
		dialog.afterClosed().subscribe(newNodeName => {
			if(newNodeName) {
				this.loadNodes();
			}
		});
	}

	public visualizeReadings() {
		this.dialog.open(VisualizeReadingDialogComponent, {
			data: {
				nodeName: null,
				sensorName: null,
				readings: null
			}
		});
	}
	
}
