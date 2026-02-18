import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { IonicModule } from '@ionic/angular';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';


@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  styleUrls: ['./register.page.scss'],
  standalone: true,
  imports: [CommonModule, IonicModule, FormsModule, ReactiveFormsModule]
})
export class RegisterPage implements OnInit {

  registerForm!: FormGroup;
  otpForm!: FormGroup;

  isOtpStep = false;
  isPasswordVisible = false;

  timer = 60;
  interval: any;

  constructor(private fb: FormBuilder) {}

  ngOnInit() {
    this.registerForm = this.fb.group({
      username: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', [Validators.required, Validators.minLength(8)]],
      password: ['', [Validators.required, Validators.minLength(6)]],
    });

    this.otpForm = this.fb.group({
      d0: ['', Validators.required],
      d1: ['', Validators.required],
      d2: ['', Validators.required],
      d3: ['', Validators.required],
      d4: ['', Validators.required],
      d5: ['', Validators.required],
    });
  }

  get otpControls() {
    return Object.values(this.otpForm.controls) as FormControl[];
  }

  togglePasswordVisibility() {
    this.isPasswordVisible = !this.isPasswordVisible;
  }

  onRegister() {
    if (this.registerForm.invalid) return;

    // API REGISTER + SEND OTP
    console.log(this.registerForm.value);

    this.isOtpStep = true;
    this.startTimer();
  }

  verifyOtp() {
    if (this.otpForm.invalid) return;

    const otp = Object.values(this.otpForm.value).join('');
    console.log('OTP:', otp);

    // API VERIFY OTP
  }

  resendOtp() {
    if (this.timer > 0) return;

    // API RESEND OTP
    this.startTimer();
  }

  startTimer() {
    this.timer = 60;
    clearInterval(this.interval);

    this.interval = setInterval(() => {
      this.timer--;
      if (this.timer === 0) {
        clearInterval(this.interval);
      }
    }, 1000);
  }

  moveFocus(event: any, index: number) {
    const input = event.target;
    if (input.value && index < 5) {
      input.nextElementSibling?.focus();
    }
  }
}