import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: false,
})
export class HomePage {

  isPasswordVisible: boolean = false;
  isLogin: boolean = true; // Toggle between login and signup
  phoneNumber: string = '';
  password: string = '';
  email: string = '';
  name: string = '';

  constructor(private router: Router) {}

  togglePasswordVisibility() {
    this.isPasswordVisible = !this.isPasswordVisible;
  }

  toggleMode() {
    this.isLogin = !this.isLogin;
    // Reset form when switching modes
    this.phoneNumber = '';
    this.password = '';
    this.email = '';
    this.name = '';
  }

  onLogin() {
    console.log('Login attempted with:', {
      phone: this.phoneNumber,
      password: this.password
    });

    this.router.navigate(['/map']);
  }

  onSignup() {
    console.log('Signup attempted with:', {
      name: this.name,
      email: this.email,
      phone: this.phoneNumber,
      password: this.password
    });
  }

  onForgotPassword() {
    console.log('Forgot password clicked');
  }

  onSocialLogin(provider: string) {
    console.log('Social login with:', provider);
  }

}
