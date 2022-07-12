import { Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable } from 'rxjs';
import { ConfirmDialogComponent } from 'src/app/dialogs/confirm-dialog/confirm-dialog.component';
import { CreateExportDialogComponent } from 'src/app/dialogs/create-export-dialog/create-export-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-export-list',
  templateUrl: './export-list.component.html',
  styleUrls: ['./export-list.component.css']
})
export class ExportListComponent implements OnInit {
	public displayedColumns: string[] = ['enabled', 'protocol', 'format', 'url', 'authentication', 'failCount', 'status', 'actions'];
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
		this.loadExports();
  	}
	
	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadExports() {
		this.server.getExports().subscribe({
			next: (exports: any) => {
				this.dataSource.data = exports;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public changedValue(exportConfig: any, property: string, newValue: any) {
		this.server.updateExport(exportConfig.id, property, newValue).subscribe({
			next: (response: any) => {
				exportConfig[property] = newValue;
				this.loadExports();
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public createExport() {
		const dialog = this.dialog.open(CreateExportDialogComponent);
		dialog.afterClosed().subscribe(created => {
			if(created) {
				this.loadExports();
			}
		});
	}

	public deleteExport(exportConfig: any) {
		const dialog = this.dialog.open(ConfirmDialogComponent, {
			data: {
				title: "Delete Export",
				text: "This will delete this export configuration.",
				action: new Observable(
					observer => {
						this.server.deleteExport(exportConfig.id).subscribe({
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
				this.loadExports();
			}
		});
	}

}
