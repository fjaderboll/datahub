import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { CreateDatasetDialogComponent } from 'src/app/dialogs/create-dataset-dialog/create-dataset-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
	public swaggerUrl: string;
	public datasets: any;
	public totalDatasetSize: string;

	constructor(
		private server: ServerService,
		private dialog: MatDialog
	) { }

	ngOnInit(): void {
		this.swaggerUrl = environment.apiUrl;
		this.loadDatasets();
	}

	private loadDatasets() {
		this.server.getDatasets().subscribe({
			next: (datasets: any) => {
				this.datasets = datasets;
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
