import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
import { ServerService } from 'src/app/services/server.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
	public swaggerUrl: string;
	public nodes: any;
	public totalDatasetSize: string;

	constructor(
		private server: ServerService,
		private dialog: MatDialog
	) { }

	ngOnInit(): void {
		this.swaggerUrl = environment.apiUrl;
		this.loadNodes();
	}

	private loadNodes() {
		this.server.getNodes().subscribe({
			next: (nodes: any) => {
				this.nodes = nodes;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
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
