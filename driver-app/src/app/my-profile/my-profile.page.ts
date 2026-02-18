import { Component } from '@angular/core';
import { IonicModule } from '@ionic/angular';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-my-profile',
  templateUrl: './my-profile.page.html',
  styleUrls: ['./my-profile.page.scss'],
  standalone: true,
  imports: [IonicModule, CommonModule]
})
export class MyProfilePage {
  
  constructor(private router: Router) {}

  // Add your methods here for handling button clicks
  onEditProfile() {
    console.log('Edit profile clicked');
    // Navigate to edit profile page
  }

  onAddFunds() {
    console.log('Add funds clicked');
    // Navigate to add funds page
  }

  onLogout() {
    console.log('Logout clicked');
    // Implement logout logic
  }

  onMenuItemClick(item: string) {
    console.log(`${item} clicked`);
    // Handle menu item clicks
  }


  logout() {
    // Implémentez votre logique de déconnexion ici
    console.log('Déconnexion...');
    
    // Exemple de déconnexion :
    // this.authService.logout();
     this.router.navigate(['/']);
    
    // Pour l'instant, affichons une alerte
    //alert('Fonction de déconnexion à implémenter');
  }

}