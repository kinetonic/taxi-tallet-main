import { NgModule } from '@angular/core';
import { MyProfilePage } from './my-profile.page';
import { MyProfilePageRoutingModule } from './my-profile-routing.module';

@NgModule({ 
  imports: [
    MyProfilePage,
    MyProfilePageRoutingModule
  ]
})
export class MyProfilePageModule {}