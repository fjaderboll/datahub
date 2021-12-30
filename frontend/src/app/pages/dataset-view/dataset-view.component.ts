import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ServerService } from 'src/app/services/server.service';
import { UtilsService } from 'src/app/services/utils.service';

@Component({
  selector: 'app-dataset-view',
  templateUrl: './dataset-view.component.html',
  styleUrls: ['./dataset-view.component.css']
})
export class DatasetViewComponent implements OnInit {
	public dataset: any;

	constructor(
		private utils: UtilsService,
		private server: ServerService,
		private route: ActivatedRoute
	) { }

	ngOnInit(): void {
		this.loadDataset();
	}

	private loadDataset() {
		let name = this.route.snapshot.paramMap.get('name') || 'this should never happen';

		this.server.getDataset(name).subscribe({
			next: (dataset: any) => {
				this.dataset = dataset;
				this.dataset.sizeStr = this.utils.printFilesize(this.dataset.size);
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

}
