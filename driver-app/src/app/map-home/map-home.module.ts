import { NgModule } from '@angular/core';
import { MapHomePage } from './map-home.page';
import { MapHomePageRoutingModule } from './map-home-routing.module';

@NgModule({
  imports: [
    MapHomePage,
    MapHomePageRoutingModule
  ]
})
export class MapHomePageModule {}