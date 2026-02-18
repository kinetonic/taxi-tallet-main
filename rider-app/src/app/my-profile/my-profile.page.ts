// my-profile.page.ts
import { Component, OnInit } from '@angular/core';
import { IonicModule } from '@ionic/angular';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AlertController, LoadingController } from '@ionic/angular';
import { AuthService, User } from '../services/auth.service';

@Component({
  selector: 'app-my-profile',
  templateUrl: './my-profile.page.html',
  styleUrls: ['./my-profile.page.scss'],
  standalone: true,
  imports: [IonicModule, CommonModule]
})
export class MyProfilePage implements OnInit {
  
  user: User | null = null;
  isLoading: boolean = true;

  constructor(
    private router: Router,
    private authService: AuthService,
    private alertController: AlertController,
    private loadingController: LoadingController
  ) {}

  ngOnInit() {
    this.loadUserData();
  }

  ionViewWillEnter() {
    this.loadUserData();
  }

  loadUserData() {
    this.isLoading = true;
    this.user = this.authService.getCurrentUser();
    this.isLoading = false;
  }

  // Navigation methods
  onEditProfile() {
    console.log('Edit profile clicked');
    this.router.navigate(['/edit-profile']);
  }

  onHistory() {
    console.log('History clicked');
    this.router.navigate(['/trip-history']);
  }

  onFavorites() {
    console.log('Favorites clicked');
    this.router.navigate(['/favorite-addresses']);
  }

  onBilling() {
    console.log('Billing clicked');
    this.router.navigate(['/billing']);
  }

  onAddresses() {
    console.log('Addresses clicked');
    this.router.navigate(['/addresses']);
  }

  onPaymentMethods() {
    console.log('Payment methods clicked');
    this.router.navigate(['/payment-methods']);
  }

  onNotifications() {
    console.log('Notifications clicked');
    this.router.navigate(['/notifications']);
  }

  onLanguage() {
    console.log('Language clicked');
    this.router.navigate(['/language']);
  }

  onHelp() {
    console.log('Help clicked');
    this.router.navigate(['/help-support']);
  }

  onReportProblem() {
    console.log('Report problem clicked');
    this.router.navigate(['/report-problem']);
  }

  onRateApp() {
    console.log('Rate app clicked');
    // Open app store or rating dialog
  }

  onTerms() {
    console.log('Terms clicked');
    this.router.navigate(['/terms']);
  }

  onPrivacy() {
    console.log('Privacy clicked');
    this.router.navigate(['/privacy']);
  }

  async logout() {
    const alert = await this.alertController.create({
      header: 'Confirmation',
      message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
      buttons: [
        {
          text: 'Annuler',
          role: 'cancel',
          cssClass: 'secondary'
        },
        {
          text: 'Déconnexion',
          handler: async () => {
            await this.performLogout();
          }
        }
      ]
    });

    await alert.present();
  }

  async performLogout() {
    const loading = await this.loadingController.create({
      message: 'Déconnexion en cours...',
      spinner: 'crescent'
    });
    
    await loading.present();
    
    try {
      // Clear any pending operations if needed
      
      // Call logout from auth service
      this.authService.logout();
      
      await loading.dismiss();
      
      // Navigate to home page
      this.router.navigate(['/home']);
      
    } catch (error) {
      await loading.dismiss();
      console.error('Logout error:', error);
      
      const errorAlert = await this.alertController.create({
        header: 'Erreur',
        message: 'Une erreur est survenue lors de la déconnexion.',
        buttons: ['OK']
      });
      
      await errorAlert.present();
    }
  }

  // Helper methods for template
  getUserName(): string {
    return this.authService.getUserName();
  }

  getUserEmail(): string {
    return this.authService.getUserEmail();
  }

  getUserPhone(): string {
    return this.authService.getUserPhone();
  }

  getUserType(): string {
    const userType = this.authService.getUserType();
    switch(userType) {
      case 'rider': return 'Passager';
      case 'driver': return 'Chauffeur';
      default: return 'Utilisateur';
    }
  }

  getProfilePicture(): string {
    return this.authService.getProfilePicture();
  }

  hasProfilePicture(): boolean {
    return !!this.getProfilePicture();
  }
}