import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-user-list',
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.css']
})
export class UserListComponent implements OnInit, AfterViewInit {
	displayedColumns: string[] = ['username', 'email', 'admin', 'datasetsCount', 'datasetsSize'];
  	dataSource = new MatTableDataSource<any>();

	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

  	constructor(
		public auth: AuthenticationService,
		private server: ServerService,
		private utils: UtilsService
	) { }

  	ngOnInit(): void {
		this.server.getUsers().subscribe({
			next: (v: any) => {
				v.forEach((user: any) => {
					user.datasetsSizeStr = this.utils.printFilesize(user.datasetsSize);
				});
				this.dataSource.data = v;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
  	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	public createUser() {
		console.log('TODO create');
	}

}
