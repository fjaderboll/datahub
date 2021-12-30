import { Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ActivatedRoute } from '@angular/router';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-dataset-view',
  templateUrl: './dataset-view.component.html',
  styleUrls: ['./dataset-view.component.css']
})
export class DatasetViewComponent implements OnInit {
	public dataset: any;

	public displayedColumns: string[] = ['name', 'desc', 'sensorCount', 'lastReadingTimestamp'];
	public dataSource = new MatTableDataSource<any>();
	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

	constructor(
		private utils: UtilsService,
		private server: ServerService,
		private route: ActivatedRoute,
		private dialog: MatDialog
	) { }

	ngOnInit(): void {
		this.loadDataset();
	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadDataset() {
		let name = this.route.snapshot.paramMap.get('name') || 'this should never happen';

		this.server.getDataset(name).subscribe({
			next: (dataset: any) => {
				this.dataset = dataset;
				this.dataset.sizeStr = this.utils.printFilesize(this.dataset.size);
				this.dataSource.data = this.dataset.nodes;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public changedValue(property: string, newValue: any) {
		this.server.updateDataset(this.dataset.name, property, newValue).subscribe({
			next: (response: any) => {
				this.dataset[property] = newValue;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public createNode() {
		const dialog = this.dialog.open(CreateNodeDialogComponent, {
			data: {
				datasetName: this.dataset.name
			}
		});
		dialog.afterClosed().subscribe(newName => {
			if(newName) {
				this.loadDataset();
			}
		});
	}

}
