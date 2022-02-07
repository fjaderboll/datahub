import { NgModule } from '@angular/core';
import { Routes, RouterModule, PreloadAllModules } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ExportListComponent } from './pages/export-list/export-list.component';
import { LoginComponent } from './pages/login/login.component';
import { NodeListComponent } from './pages/node-list/node-list.component';
import { NodeViewComponent } from './pages/node-view/node-view.component';
import { SensorViewComponent } from './pages/sensor-view/sensor-view.component';
import { TokenListComponent } from './pages/token-list/token-list.component';
import { UserListComponent } from './pages/user-list/user-list.component';
import { UserViewComponent } from './pages/user-view/user-view.component';
import { AuthGuardService } from './services/auth-guard.service';

const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { path: 'nodes', component: NodeListComponent, canActivate: [AuthGuardService] },
  { path: 'nodes/:name', component: NodeViewComponent, canActivate: [AuthGuardService] },
  { path: 'nodes/:nodeName/sensors/:sensorName', component: SensorViewComponent, canActivate: [AuthGuardService] },
  { path: 'tokens', component: TokenListComponent, canActivate: [AuthGuardService] },
  { path: 'exports', component: ExportListComponent, canActivate: [AuthGuardService] },
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
