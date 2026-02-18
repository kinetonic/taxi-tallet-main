import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { Router } from '@angular/router';
import { Platform } from '@ionic/angular';

export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  type: 'rider' | 'driver' | 'admin';
  profile_picture?: string;
  is_active: boolean;
  is_suspended: boolean;
  suspended_until?: string;
  suspension_reason?: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    token: string;
  };
}

export interface LoginRequest {
  email: string;
  password: string;
  phone?: string;
  device_name: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject: BehaviorSubject<User | null>;
  public currentUser: Observable<User | null>;
  private tokenKey = 'auth_token';
  private userKey = 'current_user';
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  public isAuthenticated = this.isAuthenticatedSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    private platform: Platform
  ) {
    this.currentUserSubject = new BehaviorSubject<User | null>(null);
    this.currentUser = this.currentUserSubject.asObservable();
    this.checkToken();
  }

  private checkToken() {
    const token = this.getTokenFromLocalStorage();
    const user = this.getUserFromLocalStorage();
    
    if (token && user) {
      this.currentUserSubject.next(user);
      this.isAuthenticatedSubject.next(true);
    }
  }

  /**
   * Get token from localStorage
   */
  private getTokenFromLocalStorage(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  /**
   * Get user from localStorage
   */
  private getUserFromLocalStorage(): User | null {
    const userStr = localStorage.getItem(this.userKey);
    return userStr ? JSON.parse(userStr) : null;
  }

  /**
   * Save token to localStorage
   */
  private saveTokenToLocalStorage(token: string): void {
    localStorage.setItem(this.tokenKey, token);
  }

  /**
   * Save user to localStorage
   */
  private saveUserToLocalStorage(user: User): void {
    localStorage.setItem(this.userKey, JSON.stringify(user));
  }

  /**
   * Remove token from localStorage
   */
  private removeTokenFromLocalStorage(): void {
    localStorage.removeItem(this.tokenKey);
  }

  /**
   * Remove user from localStorage
   */
  private removeUserFromLocalStorage(): void {
    localStorage.removeItem(this.userKey);
  }

  /**
   * Clear all auth data from localStorage
   */
  private clearAuthDataFromLocalStorage(): void {
    this.removeTokenFromLocalStorage();
    this.removeUserFromLocalStorage();
  }

  /**
   * Login user
   */
  login(credentials: { email: string; password: string }): Observable<LoginResponse> {
    const deviceName = this.getDeviceName();
    
    const request: LoginRequest = {
      email: credentials.email,
      password: credentials.password,
      device_name: deviceName
    };

    return this.http.post<LoginResponse>('http://localhost:8000/api/auth/login', request)
      .pipe(
        tap(response => {
          if (response.success && response.data) {
            this.saveTokenToLocalStorage(response.data.token);
            this.saveUserToLocalStorage(response.data.user);
            this.currentUserSubject.next(response.data.user);
            this.isAuthenticatedSubject.next(true);
          }
        })
      );
  }

  /**
   * Get current user value
   */
  public get currentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  /**
   * Get current token
   */
  getToken(): string | null {
    return this.getTokenFromLocalStorage();
  }

  /**
   * Check if user is logged in
   */
  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  /**
   * Logout user
   */
  logout(): void {
    const token = this.getToken();
    
    if (token) {
      // Call logout API if needed
      // this.http.post('http://localhost:8000/api/logout', {}).subscribe();
    }
    
    this.clearAuthDataFromLocalStorage();
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    this.router.navigate(['/home']);
  }

  /**
   * Forgot password
   */
  forgotPassword(email: string): Observable<any> {
    return this.http.post('http://localhost:8000/api/auth/forgot-password', { email });
  }

  /**
   * Get device name for API
   */
  private getDeviceName(): string {
    if (this.platform.is('ios')) {
      return 'iOS App';
    } else if (this.platform.is('android')) {
      return 'Android App';
    } else if (this.platform.is('desktop')) {
      return 'Web Browser';
    } else {
      return 'Unknown Device';
    }
  }

  /**
   * Get user type
   */
  getUserType(): string | null {
    return this.currentUserValue?.type || null;
  }

  /**
   * Check if user is rider
   */
  isRider(): boolean {
    return this.currentUserValue?.type === 'rider';
  }

  /**
   * Check if user is driver
   */
  isDriver(): boolean {
    return this.currentUserValue?.type === 'driver';
  }

  /**
   * Check if user is admin
   */
  isAdmin(): boolean {
    return this.currentUserValue?.type === 'admin';
  }

  /**
   * Update user data in storage
   */
  updateUserData(user: User): void {
    this.saveUserToLocalStorage(user);
    this.currentUserSubject.next(user);
  }

  /**
   * Refresh token (if your API supports it)
   */
  refreshToken(): Observable<any> {
    return this.http.post('http://localhost:8000/api/auth/refresh-token', {
      token: this.getToken()
    }).pipe(
      tap((response: any) => {
        if (response.success && response.data?.token) {
          this.saveTokenToLocalStorage(response.data.token);
        }
      })
    );
  }

  /**
   * Check if token is expired (basic implementation)
   */
  isTokenExpired(): boolean {
    const token = this.getToken();
    if (!token) return true;

    // Basic check - you might want to decode JWT and check expiry
    // For now, just check if token exists
    return false;
  }

  /**
   * Get authentication headers for HTTP requests
   */
  getAuthHeaders(): { [header: string]: string } {
    const token = this.getToken();
    return {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };
  }



  /**
 * Get current user data
 */
getCurrentUser(): User | null {
  return this.currentUserValue;
}

/**
 * Get user's full name
 */
getUserName(): string {
  return this.currentUserValue?.first_name + ' ' + this.currentUserValue?.last_name || 'Utilisateur';
}

/**
 * Get user's email
 */
getUserEmail(): string {
  return this.currentUserValue?.email || '';
}

/**
 * Get user's phone
 */
getUserPhone(): string {
  return this.currentUserValue?.phone || '';
}

/**
 * Get user's profile picture URL
 */
getProfilePicture(): string {
  return this.currentUserValue?.profile_picture || '';
}

/**
 * Check if user is verified
 */
isEmailVerified(): boolean {
  return !!this.currentUserValue?.email_verified_at;
}

/**
 * Get user account status
 */
getAccountStatus(): { isActive: boolean; isSuspended: boolean } {
  return {
    isActive: this.currentUserValue?.is_active || false,
    isSuspended: this.currentUserValue?.is_suspended || false
  };
}


}