import { AfterViewInit, Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ActivatedRoute } from '@angular/router';
import { AuthenticationService } from 'src/app/services/authentication.service';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
	selector: 'app-node-view',
	templateUrl: './node-view.component.html',
	styleUrls: ['./node-view.component.css']
})
export class NodeViewComponent implements OnInit, AfterViewInit {
	public node: any;

	public displayedColumns: string[] = ['name', 'readingCount', 'lastReadingTimestamp', 'desc'];
	public dataSource = new MatTableDataSource<any>();
	@ViewChild(MatPaginator) paginator: MatPaginator;
	@ViewChild(MatSort) sort: MatSort;

	constructor(
		public auth: AuthenticationService,
		public utils: UtilsService,
		private server: ServerService,
		private route: ActivatedRoute
	) { }

	ngOnInit(): void {
		this.loadNode();
	}

	ngAfterViewInit() {
		this.dataSource.paginator = this.paginator;
		this.dataSource.sort = this.sort;
	}

	private loadNode() {
		let name = this.route.snapshot.paramMap.get('name') || 'this should never happen';

		this.server.getNode(name).subscribe({
			next: (node: any) => {
				this.node = node;
				this.dataSource.data = node.sensors;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public changedValue(property: string, newValue: any) {
		this.server.updateNode(this.node.username, property, newValue).subscribe({
			next: (response: any) => {
				this.node[property] = newValue;
			},
			error: (e) => {
				this.server.showHttpError(e);
			}
		});
	}

	public deleteNode() {

	}

	public createSensor() {
		
	}
}
