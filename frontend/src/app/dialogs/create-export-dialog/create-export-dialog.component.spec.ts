import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateExportDialogComponent } from './create-export-dialog.component';

describe('CreateExportDialogComponent', () => {
  let component: CreateExportDialogComponent;
  let fixture: ComponentFixture<CreateExportDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateExportDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateExportDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
