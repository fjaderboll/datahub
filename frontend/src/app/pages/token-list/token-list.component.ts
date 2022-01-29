import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { CreateNodeDialogComponent } from 'src/app/dialogs/create-node-dialog/create-node-dialog.component';
import { CreateTokenDialogComponent } from 'src/app/dialogs/create-token-dialog/create-token-dialog.component';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-token-list',
	templateUrl: './token-list.component.html',
	styleUrls: ['./token-list.component.css']
})
export class TokenListComponent implements OnInit, AfterViewInit {
	public displayedColumns: string[] = ['desc', 'enabled', 'read', 'write', 'token'];
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
		this.loadTokens();
  	}
	
	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadTokens() {
		this.server.getTokens().subscribe({
			next: (tokens: any) => {
				this.dataSource.data = tokens;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public createToken() {
		const dialog = this.dialog.open(CreateTokenDialogComponent);
		dialog.afterClosed().subscribe(created => {
			if(created) {
				this.loadTokens();
			}
		});
	}
}