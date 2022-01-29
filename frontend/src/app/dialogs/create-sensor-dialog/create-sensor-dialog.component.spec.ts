import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateSensorDialogComponent } from './create-sensor-dialog.component';

describe('CreateSensorDialogComponent', () => {
  let component: CreateSensorDialogComponent;
  let fixture: ComponentFixture<CreateSensorDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateSensorDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateSensorDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
