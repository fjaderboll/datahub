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
		this.toastr.error(message);
		console.log(message);
	}

}
