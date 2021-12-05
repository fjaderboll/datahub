import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { LoginComponent } from './pages/login/login.component';
import { DatasetViewComponent } from './pages/dataset-view/dataset-view.component';
import { DatasetListComponent } from './pages/dataset-list/dataset-list.component';
import { UserViewComponent } from './pages/user-view/user-view.component';
import { UserListComponent } from './pages/user-list/user-list.component';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { MatIconModule } from '@angular/material/icon';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { HttpClientModule } from '@angular/common/http';
import { ToastrModule } from 'ngx-toastr';
import { FormsModule } from '@angular/forms';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatSortModule } from '@angular/material/sort';
import { MatDialogModule } from '@angular/material/dialog';
import { CreateUserDialogComponent } from './dialogs/create-user-dialog/create-user-dialog.component';

@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    DatasetViewComponent,
    DatasetListComponent,
    UserViewComponent,
    UserListComponent,
    HeaderComponent,
    FooterComponent,
    DashboardComponent,
    CreateUserDialogComponent
  ],
  imports: [
    BrowserModule,
	HttpClientModule,
    AppRoutingModule,
	BrowserAnimationsModule,
	ToastrModule.forRoot({
		positionClass: 'toast-bottom-right',
		preventDuplicates: true
	}),
	MatIconModule,
	MatToolbarModule,
	MatButtonModule,
	MatFormFieldModule,
	MatInputModule,
	FormsModule,
	MatTableModule,
	MatPaginatorModule,
	MatSortModule,
	MatDialogModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
