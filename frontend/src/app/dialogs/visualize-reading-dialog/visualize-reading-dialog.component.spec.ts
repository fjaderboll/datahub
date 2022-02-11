import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VisualizeReadingDialogComponent } from './visualize-reading-dialog.component';

describe('VisualizeReadingDialogComponent', () => {
  let component: VisualizeReadingDialogComponent;
  let fixture: ComponentFixture<VisualizeReadingDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ VisualizeReadingDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(VisualizeReadingDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
