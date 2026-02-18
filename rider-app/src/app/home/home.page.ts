import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { LoadingController, AlertController } from '@ionic/angular';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: false,
})
export class HomePage implements OnInit {
  
  isPasswordVisible: boolean = false;
  isLogin: boolean = true;
  phoneNumber: string = '';
  password: string = '';
  email: string = '';
  name: string = '';

  constructor(
    private router: Router,
    private authService: AuthService,
    private loadingCtrl: LoadingController,
    private alertCtrl: AlertController
  ) {}

  ngOnInit() {
    // Check if user is already logged in
    this.checkExistingSession();
  }

  async checkExistingSession() {
    const isLoggedIn = await this.authService.isLoggedIn();
    if (isLoggedIn) {
      const userType = this.authService.getUserType();
      if (userType === 'rider' || userType === 'driver') {
        this.router.navigate(['/map']);
      } else if (userType === 'admin') {
        this.router.navigate(['/admin']);
      }
    }
  }

  togglePasswordVisibility() {
    this.isPasswordVisible = !this.isPasswordVisible;
  }

  async onLogin() {
    if (!this.email || !this.password) {
      this.showAlert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    const loading = await this.loadingCtrl.create({
      message: 'Connexion en cours...',
      spinner: 'crescent'
    });
    await loading.present();

    try {
      const response = await this.authService.login({
        email: this.email,
        password: this.password
      }).toPromise();

      if (response?.success) {
        loading.dismiss();
        
        // Check user status
        const user = response?.data.user;
        
        if (!user.is_active) {
          this.showAlert(
            'Compte désactivé',
            'Votre compte a été désactivé. Veuillez contacter le support.'
          );
          return;
        }

        if (user.is_suspended) {
          let message = 'Votre compte est suspendu';
          if (user.suspended_until) {
            const date = new Date(user.suspended_until);
            message += ` jusqu'au ${date.toLocaleDateString()}`;
          }
          if (user.suspension_reason) {
            message += `. Raison: ${user.suspension_reason}`;
          }
          
          this.showAlert('Compte suspendu', message);
          return;
        }

        // Navigate based on user type
        this.router.navigate(['/map']);

      } else {
        loading.dismiss();
        this.showAlert('Erreur de connexion', response?.message || 'Identifiants invalides');
      }
    } catch (error: any) {
      loading.dismiss();
      console.error('Login error:', error);
      
      let errorMessage = 'Une erreur est survenue';
      if (error.status === 401) {
        errorMessage = 'Email ou mot de passe incorrect';
      } else if (error.error?.message) {
        errorMessage = error.error.message;
      }
      
      this.showAlert('Erreur', errorMessage);
    }
  }

  async onForgotPassword() {
    if (!this.email) {
      this.showAlert('Information', 'Veuillez entrer votre email pour réinitialiser votre mot de passe');
      return;
    }

    const alert = await this.alertCtrl.create({
      header: 'Mot de passe oublié',
      message: `Un lien de réinitialisation sera envoyé à ${this.email}`,
      buttons: [
        {
          text: 'Annuler',
          role: 'cancel'
        },
        {
          text: 'Envoyer',
          handler: async () => {
            const loading = await this.loadingCtrl.create({
              message: 'Envoi en cours...',
              spinner: 'crescent'
            });
            await loading.present();

            try {
              await this.authService.forgotPassword(this.email).toPromise();
              loading.dismiss();
              this.showAlert('Succès', 'Un email de réinitialisation a été envoyé');
            } catch (error) {
              loading.dismiss();
              this.showAlert('Erreur', 'Impossible d\'envoyer l\'email de réinitialisation');
            }
          }
        }
      ]
    });

    await alert.present();
  }

  onSocialLogin(provider: string) {
    console.log('Social login with:', provider);
    // Implement social login here
  }

  private async showAlert(header: string, message: string) {
    const alert = await this.alertCtrl.create({
      header,
      message,
      buttons: ['OK']
    });
    await alert.present();
  }


  registerPage(){
    this.router.navigate(['/register']);
  }
}