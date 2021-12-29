import { NgModule } from '@angular/core';
import { Routes, RouterModule, PreloadAllModules } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { DatasetListComponent } from './pages/dataset-list/dataset-list.component';
import { DatasetViewComponent } from './pages/dataset-view/dataset-view.component';
import { LoginComponent } from './pages/login/login.component';
import { UserListComponent } from './pages/user-list/user-list.component';
import { UserViewComponent } from './pages/user-view/user-view.component';
import { AuthGuardService } from './services/auth-guard.service';

const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { path: 'datasets', component: DatasetListComponent, canActivate: [AuthGuardService] },
  { path: 'datasets/:name', component: DatasetViewComponent, canActivate: [AuthGuardService] },
  { path: 'users', component: UserListComponent, canActivate: [AuthGuardService] },
  { path: 'users/:username', component: UserViewComponent, canActivate: [AuthGuardService] },
  { path: '', component: DashboardComponent, canActivate: [AuthGuardService] },
  { path: '**', redirectTo: '', canActivate: [AuthGuardService] }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {
    preloadingStrategy: PreloadAllModules,
    relativeLinkResolution: 'legacy'
})],
  exports: [RouterModule]
})
export class AppRoutingModule {}
