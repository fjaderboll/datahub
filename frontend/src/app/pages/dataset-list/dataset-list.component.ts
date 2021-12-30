import { Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { CreateDatasetDialogComponent } from 'src/app/dialogs/create-dataset-dialog/create-dataset-dialog.component';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-dataset-list',
	templateUrl: './dataset-list.component.html',
	styleUrls: ['./dataset-list.component.css']
})
export class DatasetListComponent implements OnInit {
	public displayedColumns: string[] = ['name', 'desc', 'size'];
	public dataSource = new MatTableDataSource<any>();

	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

	constructor(
		private server: ServerService,
		private utils: UtilsService,
		private dialog: MatDialog
	) { }

	ngOnInit(): void {
		this.loadDatasets();
	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadDatasets() {
		this.server.getDatasets().subscribe({
			next: (v: any) => {
				v.forEach((dataset: any) => {
					dataset.sizeStr = this.utils.printFilesize(dataset.size);
				});
				this.dataSource.data = v;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public createDataset() {
		const dialog = this.dialog.open(CreateDatasetDialogComponent);
		dialog.afterClosed().subscribe(newName => {
			if(newName) {
				this.loadDatasets();
			}
		});
	}

}
