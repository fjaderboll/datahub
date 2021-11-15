import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ServerService } from 'src/app/services/server.service';

@Component({
  selector: 'app-user-list',
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.css']
})
export class UserListComponent implements OnInit, AfterViewInit {
	displayedColumns: string[] = ['username', 'email', 'admin'];
  	dataSource = new MatTableDataSource<any>();

	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

  	constructor(
		private server: ServerService
	  ) { }

  	ngOnInit(): void {
		this.server.getUsers().subscribe({
			next: (v: any) => {
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

}
