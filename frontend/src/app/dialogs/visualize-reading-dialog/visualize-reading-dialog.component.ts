import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import * as Highcharts from 'highcharts';
import { ServerService } from 'src/app/services/server.service';

@Component({
	selector: 'app-visualize-reading-dialog',
	templateUrl: './visualize-reading-dialog.component.html',
	styleUrls: ['./visualize-reading-dialog.component.css']
})
export class VisualizeReadingDialogComponent implements OnInit {
	public readingsLimit = 1000;

	constructor(
		public dialogRef: MatDialogRef<VisualizeReadingDialogComponent>,
		@Inject(MAT_DIALOG_DATA) public data: any,
		private server: ServerService
	) {	}

	ngOnInit(): void {
		if(this.data.readings) {
			this.readingsLimit = this.data.readings.length;
			this.drawChart();
		} else {
			this.loadReadings();
		}
	}

	private drawChart() {
		let invalidValueCount = 0;
		let yAxis: any = [];
		let series: any = [];
		this.data.readings.forEach((reading: any) => {
			if(this.isValueValid(reading.value)) {
				let serie: any = series.find((s: any) => {
					return s.name == reading.sensorName;
				});
				if(!serie) {
					let i: any = yAxis.findIndex((ya: any) => {
						return ya.title.text == reading.unit;
					});
					if(i < 0) {
						yAxis.push({
							title: {
								text: reading.unit
							}
						});
						i = yAxis.length - 1;
					}

					serie = {
						name: reading.sensorName,
						yAxis: i,
						data: []
					};
					series.push(serie);
				}

				let t = new Date(reading.timestamp).getTime();
				serie.data.push([t, reading.value]);
			} else {
				invalidValueCount++;
			}
		});

		series.forEach((serie: any) => {
			serie.data.sort((r1: any, r2: any) => {
				return r1[0] - r2[0];
			});
		});

		const options: any = {
			chart: {
                zoomType: 'x'
            },
			title: {
				text: this.data.nodeName + (this.data.sensorName ? ' - ' + this.data.sensorName : '') + ' - ' + this.data.readings.length + ' readings'
			},
			subtitle: {
				text: (invalidValueCount ? invalidValueCount + ' non-numeric value' + (invalidValueCount == 1 ? '' : 's') + ' ignored' : null)
			},
			xAxis: {
                type: 'datetime'
            },
			yAxis: yAxis,
            legend: {
                enabled: true
            },
			series: series
		};
		Highcharts.chart('chart', options);
	}

	private isValueValid(value: any) {
		return !isNaN(value);
	}

	private loadReadings() {
		if(this.data.sensorName) {
			this.server.getSensorReadings(this.data.nodeName, this.data.sensorName, this.readingsLimit).subscribe({
				next: (readings: any) => {
					this.data.readings = readings;
					this.drawChart();
				},
				error: (e) => {
					this.server.showHttpError(e);
				}
			});
		} else {
			this.server.getNodeReadings(this.data.nodeName, this.readingsLimit).subscribe({
				next: (readings: any) => {
					this.data.readings = readings;
					this.drawChart();
				},
				error: (e) => {
					this.server.showHttpError(e);
				}
			});
		}
	}

	public loadMore() {
		this.readingsLimit *= 2;
		this.loadReadings();
	}

}