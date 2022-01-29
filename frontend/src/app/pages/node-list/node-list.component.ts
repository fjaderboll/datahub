import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-node-list',
  templateUrl: './node-list.component.html',
  styleUrls: ['./node-list.component.css']
})
export class NodeListComponent implements OnInit, AfterViewInit {
	public displayedColumns: string[] = ['name', 'sensorCount', 'lastReadingTimestamp', 'desc'];
	public dataSource = new MatTableDataSource<any>();

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
				v.forEach((node: any) => {
					//user.databaseSizeStr = this.utils.printFilesize(user.databaseSize);
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
		dialog.afterClosed().subscribe(newUsername => {
			if(newUsername) {
				this.loadNodes();
			}
		});
	}
}
