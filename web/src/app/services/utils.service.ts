import { Injectable } from '@angular/core';
import { ToastrService } from 'ngx-toastr';

@Injectable({
	providedIn: 'root'
})
export class UtilsService {

	constructor(
		private toastr: ToastrService
	) { }

	public toastSuccess(message: string) {
		this.toastr.success(message);
	}

	public toastError(message: string) {
		console.log(message);
		this.toastr.error(message);
	}

	public printFilesize = function(bytes: number, decimals?: number) {
		if(bytes == null) return '';
		if(bytes === 0) return '0 Bytes';
		if(bytes === 1) return '1 Byte';

		let k = 1024; // or 1024 for binary
		let sizes = ['Bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		let decimalArray = [0, 0, 1, 2, 3, 3, 3, 3, 3];

		let i = Math.floor(Math.log(bytes) / Math.log(k));
		let dm = decimals ? decimals : decimalArray[i];
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	};

}
