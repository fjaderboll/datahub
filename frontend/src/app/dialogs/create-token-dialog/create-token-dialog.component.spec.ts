import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateTokenDialogComponent } from './create-token-dialog.component';

describe('CreateTokenDialogComponent', () => {
  let component: CreateTokenDialogComponent;
  let fixture: ComponentFixture<CreateTokenDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateTokenDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateTokenDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
