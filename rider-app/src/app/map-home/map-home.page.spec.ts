import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MapHomePage } from './map-home.page';

describe('MapHomePage', () => {
  let component: MapHomePage;
  let fixture: ComponentFixture<MapHomePage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(MapHomePage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
