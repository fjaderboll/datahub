import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { LoginComponent } from './pages/login/login.component';
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
import { MatRadioModule } from '@angular/material/radio';
import { MatTabsModule } from '@angular/material/tabs';
import { MatCheckboxModule } from '@angular/material/checkbox'; 
import { MatSelectModule } from '@angular/material/select';
import { CreateUserDialogComponent } from './dialogs/create-user-dialog/create-user-dialog.component';
import { InlineEditComponent } from './components/inline-edit/inline-edit.component';
import { BreadcrumbComponent } from './components/breadcrumb/breadcrumb.component';
import { CreateNodeDialogComponent } from './dialogs/create-node-dialog/create-node-dialog.component';
import { NodeViewComponent } from './pages/node-view/node-view.component';
import { NodeListComponent } from './pages/node-list/node-list.component';
import { TokenListComponent } from './pages/token-list/token-list.component';
import { ExportListComponent } from './pages/export-list/export-list.component';
import { SensorViewComponent } from './pages/sensor-view/sensor-view.component';
import { CreateSensorDialogComponent } from './dialogs/create-sensor-dialog/create-sensor-dialog.component';
import { CreateTokenDialogComponent } from './dialogs/create-token-dialog/create-token-dialog.component';
import { ConfirmDialogComponent } from './dialogs/confirm-dialog/confirm-dialog.component';
import { VisualizeReadingDialogComponent } from './dialogs/visualize-reading-dialog/visualize-reading-dialog.component';
import { CreateReadingDialogComponent } from './dialogs/create-reading-dialog/create-reading-dialog.component';
import { CreateExportDialogComponent } from './dialogs/create-export-dialog/create-export-dialog.component';

@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    UserViewComponent,
    UserListComponent,
    HeaderComponent,
    FooterComponent,
    DashboardComponent,
    CreateUserDialogComponent,
    InlineEditComponent,
    BreadcrumbComponent,
    CreateNodeDialogComponent,
    NodeViewComponent,
    NodeListComponent,
    TokenListComponent,
    ExportListComponent,
    SensorViewComponent,
    CreateSensorDialogComponent,
    CreateTokenDialogComponent,
    ConfirmDialogComponent,
    VisualizeReadingDialogComponent,
    CreateReadingDialogComponent,
    CreateExportDialogComponent
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
	MatDialogModule,
	MatRadioModule,
	MatTabsModule,
  MatCheckboxModule,
  MatSelectModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
