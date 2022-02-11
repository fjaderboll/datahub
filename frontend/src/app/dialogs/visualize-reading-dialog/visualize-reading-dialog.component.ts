import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import * as Highcharts from 'highcharts';

@Component({
	selector: 'app-visualize-reading-dialog',
	templateUrl: './visualize-reading-dialog.component.html',
	styleUrls: ['./visualize-reading-dialog.component.css']
})
export class VisualizeReadingDialogComponent implements OnInit {
	public inputData: any;

	constructor(
		public dialogRef: MatDialogRef<VisualizeReadingDialogComponent>,
		@Inject(MAT_DIALOG_DATA) public data: any
	) {
		this.inputData = data;
	}

	ngOnInit(): void {
		this.drawChart();
	}

	private drawChart() {
		let invalidValueCount = 0;
		let data: any = [];
		this.inputData.readings.forEach((reading: any) => {
			if(this.isValueValid(reading.value)) {
				let t = new Date(reading.timestamp).getTime();
				data.push([t, reading.value]);
			} else {
				invalidValueCount++;
			}
		});
		data.sort((r1: any, r2: any) => {
			return r1[0] - r2[0];
		});

		const options: any = {
			chart: {
                zoomType: 'x'
            },
			title: {
				text: this.inputData.nodeName + ' - ' + this.inputData.sensor.name + ' - ' + this.inputData.readings.length + ' readings'
			},
			subtitle: {
				text: (invalidValueCount ? invalidValueCount + ' non-numeric value' + (invalidValueCount == 1 ? '' : 's') + ' ignored' : null)
			},
			xAxis: {
                type: 'datetime'
            },
			yAxis: {
				title: {
					text: this.inputData.sensor.unit
				}
			},
            legend: {
                enabled: false
            },
			series: [{
				name: this.inputData.sensor.name,
				data: data
			}]
		};
		Highcharts.chart('chart', options);
	}

	private isValueValid(value: any) {
		return !isNaN(value);
	}

}