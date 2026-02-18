import { Component, OnInit, AfterViewInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { FormsModule } from '@angular/forms';
import * as L from 'leaflet';
import { Geolocation } from '@capacitor/geolocation';
import { LoadingController, AlertController, Platform } from '@ionic/angular';
import { Router } from '@angular/router';
import { LocalNotifications } from '@capacitor/local-notifications';

// Define interfaces
interface RideRequest {
  id: string;
  passenger: {
    name: string;
    rating: number;
    phone: string;
  };
  pickup: {
    lat: number;
    lng: number;
    address: string;
  };
  destination: {
    lat: number;
    lng: number;
    address: string;
  };
  fare: number;
  distance: string;
  duration: string;
  requestedAt: Date;
  status: 'pending' | 'accepted' | 'completed' | 'cancelled';
}

interface DriverStatus {
  online: boolean;
  busy: boolean;
  earning: number;
  completedRides: number;
  rating: number;
}

interface Notification {
  id: string;
  type: 'ride_request' | 'ride_cancelled' | 'payment' | 'system';
  title: string;
  message: string;
  timestamp: Date;
  read: boolean;
  data?: any;
}

@Component({
  selector: 'app-map-home',
  templateUrl: './map-home.page.html',
  styleUrls: ['./map-home.page.scss'],
  standalone: true,
  imports: [CommonModule, IonicModule, FormsModule]
})
export class MapHomePage implements OnInit, AfterViewInit, OnDestroy {
  private map!: L.Map;
  private userMarker!: L.Marker;
  private watchId: string | null = null;
  private isMapInitialized = false;
  private routePolyline: L.Polyline | null = null;
  private rideRequestInterval: any = null;
  
  // Driver data
  driverStatus: DriverStatus = {
    online: false,
    busy: false,
    earning: 0,
    completedRides: 0,
    rating: 4.8
  };

  // Ride data
  currentRide: RideRequest | null = null;
  rideRequests: RideRequest[] = [];
  notifications: Notification[] = [];
  
  // Map data
  userLocation: { lat: number; lng: number } = { lat: 0, lng: 0 };
  currentAddress: string = 'Localisation en cours...';
  
  // UI states
  showNotifications: boolean = false;
  showEarnings: boolean = false;
  showRideDetails: boolean = false;

  // Computed properties for template
  get unreadNotificationsCount(): number {
    return this.notifications.filter(n => !n.read).length;
  }

  // Custom icons
  private customIcons = {
    driver: L.divIcon({
      html: `
        <div class="custom-marker driver-marker">
          <div class="driver-pulse"></div>
          <div class="marker-pin">
            <ion-icon name="car-sport"></ion-icon>
          </div>
        </div>
      `,
      iconSize: [80, 80],
      iconAnchor: [15, 42],
      className: 'driver-location-marker'
    }),
    
    pickup: L.divIcon({
      html: `
        <div class="custom-marker pickup-marker">
          <div class="marker-pin">
            <ion-icon name="location"></ion-icon>
          </div>
        </div>
      `,
      iconSize: [80, 80],
      iconAnchor: [15, 42],
      className: 'pickup-location-marker'
    }),
    
    destination: L.divIcon({
      html: `
        <div class="custom-marker destination-marker">
          <div class="marker-pin">
            <ion-icon name="flag"></ion-icon>
          </div>
        </div>
      `,
      iconSize: [80, 80],
      iconAnchor: [15, 42],
      className: 'destination-location-marker'
    })
  };

  constructor(
    private loadingController: LoadingController,
    private alertController: AlertController,
    private router: Router,
    private platform: Platform
  ) { }

  async ngOnInit() {
    await this.platform.ready();
    console.log('Platform ready');
    await this.initializeLocalNotifications();
    this.loadDriverData();
  }

  ngAfterViewInit() {
    setTimeout(() => {
      this.initMap();
    }, 300);
  }

  ngOnDestroy() {
    this.cleanupMap();
  }

  private async initializeLocalNotifications() {
    try {
      console.log('🔔 Initializing local notifications...');
      
      // Check current permission status first
      const currentPermission = await LocalNotifications.checkPermissions();
      console.log('Current notification permission:', currentPermission);
      
      if (currentPermission.display === 'granted') {
        console.log('✅ Notification permission already granted');
        return;
      }
      
      if (currentPermission.display === 'denied') {
        console.log('❌ Notification permission denied by user');
        this.showPermissionHelpAlert();
        return;
      }

      // Permission not determined - request it
      console.log('📱 Requesting notification permission...');
      const permission = await LocalNotifications.requestPermissions();
      console.log('User responded with:', permission);
      
      if (permission.display === 'granted') {
        console.log('✅ Notification permission granted by user');
      } else {
        console.log('❌ Notification permission not granted');
        this.showPermissionHelpAlert();
      }
    } catch (error) {
      console.error('Error initializing local notifications:', error);
    }
  }

  private async showPermissionHelpAlert() {
    const alert = await this.alertController.create({
      header: 'Notifications Requises',
      message: 'Pour recevoir les demandes de course en temps réel, veuillez activer les notifications dans les paramètres de votre appareil.',
      buttons: [
        {
          text: 'Compris',
          role: 'cancel'
        },
        {
          text: 'Paramètres',
          handler: () => {
            // This would typically open app settings
            console.log('User wants to open settings');
          }
        }
      ]
    });
    await alert.present();
  }

  private async showLocalNotification(title: string, body: string): Promise<void> {
    try {
      console.log('📨 Attempting to show notification:', title);
      
      // Double-check permissions before showing
      const permission = await LocalNotifications.checkPermissions();
      
      if (permission.display !== 'granted') {
        console.log('⚠️ No notification permission, using fallback');
        this.showInAppNotification(title, body);
        return;
      }

      // Generate unique ID
      const notificationId = Date.now() % 100000;
      
      await LocalNotifications.schedule({
        notifications: [
          {
            title: title,
            body: body,
            id: notificationId,
            schedule: { at: new Date(Date.now() + 100) }, // Show immediately
            // No sound = silent notification
            // No icon = uses default app icon
            extra: {
              type: 'driver_app',
              timestamp: new Date().toISOString()
            }
          }
        ]
      });
      
      console.log('✅ Local notification scheduled successfully');
      
    } catch (error) {
      console.error('❌ Error showing local notification:', error);
      // Fallback to in-app notification
      this.showInAppNotification(title, body);
    }
  }

  private async showInAppNotification(title: string, body: string) {
    try {
      const alert = await this.alertController.create({
        header: title,
        message: body,
        buttons: ['OK'],
        cssClass: 'in-app-notification',
        backdropDismiss: true
      });
      
      await alert.present();
      
      // Auto-dismiss after 4 seconds
      setTimeout(() => {
        alert.dismiss();
      }, 4000);
    } catch (error) {
      console.error('Error showing in-app notification:', error);
    }
  }

  // Test method for notifications
  async testNotification() {
    console.log('🧪 Testing notifications...');
    await this.showLocalNotification(
      'Test Notification', 
      'Ceci est une notification de test de l\'application conducteur'
    );
  }

  private initMap() {
    if (this.isMapInitialized) return;

    try {
      const defaultCoords: L.LatLngTuple = [36.8065, 10.1815];
      
      this.map = L.map('map', {
        zoomControl: false,
        preferCanvas: true
      }).setView(defaultCoords, 13);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
        minZoom: 3
      }).addTo(this.map);

      L.control.zoom({
        position: 'topright'
      }).addTo(this.map);

      this.userMarker = L.marker(defaultCoords, { 
        icon: this.customIcons.driver,
        zIndexOffset: 1000
      }).addTo(this.map);

      this.isMapInitialized = true;

      setTimeout(() => {
        this.map.invalidateSize();
        this.getCurrentLocation();
      }, 500);

    } catch (error) {
      console.error('Error initializing map:', error);
    }
  }

  async getCurrentLocation() {
    const loading = await this.loadingController.create({
      message: 'Localisation en cours...',
      spinner: 'crescent'
    });
    await loading.present();

    try {
      const permissionStatus = await Geolocation.requestPermissions();
      
      if (permissionStatus.location !== 'granted') {
        throw new Error('Location permission denied');
      }

      const coordinates = await Geolocation.getCurrentPosition({
        enableHighAccuracy: true,
        timeout: 15000
      });

      this.userLocation = {
        lat: coordinates.coords.latitude,
        lng: coordinates.coords.longitude
      };

      this.currentAddress = await this.getAddressFromCoords(
        this.userLocation.lat, 
        this.userLocation.lng
      );

      this.updateMapLocation(this.userLocation.lat, this.userLocation.lng);
      this.startWatchingPosition();

      await loading.dismiss();

    } catch (error) {
      await loading.dismiss();
      this.showLocationError();
      console.error('Error getting location:', error);
    }
  }

  private updateMapLocation(lat: number, lng: number) {
    if (!this.map) return;

    const newLocation: L.LatLngTuple = [lat, lng];
    this.map.setView(newLocation, 16);
    this.userMarker.setLatLng(newLocation);

    setTimeout(() => {
      this.map.invalidateSize();
    }, 100);
  }

  private async startWatchingPosition() {
    try {
      if (this.watchId) {
        await Geolocation.clearWatch({ id: this.watchId });
      }

      this.watchId = await Geolocation.watchPosition({
        enableHighAccuracy: true,
        timeout: 10000
      }, async (position, err) => {
        if (position && this.map) {
          this.userLocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };
          
          const newLocation: L.LatLngTuple = [this.userLocation.lat, this.userLocation.lng];
          this.userMarker.setLatLng(newLocation);
          
          this.currentAddress = await this.getAddressFromCoords(
            this.userLocation.lat, 
            this.userLocation.lng
          );
        }
        
        if (err) {
          console.error('Error watching position:', err);
        }
      });

    } catch (error) {
      console.error('Error starting position watch:', error);
    }
  }

  // Driver status management
  async toggleOnlineStatus() {
    console.log('🔄 Toggling online status. Current:', this.driverStatus.online);
    
    this.driverStatus.online = !this.driverStatus.online;
    
    if (this.driverStatus.online) {
      console.log('🚗 Driver going online');
      this.startRideRequestSimulation();
      this.showNotification('Vous êtes maintenant en ligne', 'Recherche de courses...');
      
      // Show local notification for online status
      await this.showLocalNotification(
        '🚗 Conducteur En Ligne', 
        'Vous êtes maintenant visible pour les passagers et pouvez recevoir des demandes de course.'
      );
    } else {
      console.log('🔴 Driver going offline');
      this.driverStatus.busy = false;
      this.cancelCurrentRide();
      this.showNotification('Vous êtes maintenant hors ligne', '');
      
      // Show local notification for offline status
      await this.showLocalNotification(
        '🔴 Conducteur Hors Ligne', 
        'Vous n\'êtes plus visible pour les passagers. Aucune nouvelle demande ne sera reçue.'
      );
    }
    
    // Save status to storage
    this.saveDriverData();
  }

  // Ride request simulation
  private startRideRequestSimulation() {
    // Clear any existing intervals
    if (this.rideRequestInterval) {
      clearInterval(this.rideRequestInterval);
    }

    console.log('🎯 Starting ride request simulation');
    this.rideRequestInterval = setInterval(() => {
      if (this.driverStatus.online && !this.driverStatus.busy && Math.random() > 0.7) {
        this.generateRideRequest();
      }
    }, 20000); // Every 20 seconds
  }

  private generateRideRequest() {
    const request: RideRequest = {
      id: `ride_${Date.now()}`,
      passenger: {
        name: `Passager ${Math.floor(Math.random() * 1000)}`,
        rating: Number((4.0 + Math.random() * 1.2).toFixed(1)),
        phone: `+216 ${Math.floor(20 + Math.random() * 80)} ${Math.floor(100 + Math.random() * 900)} ${Math.floor(100 + Math.random() * 900)}`
      },
      pickup: {
        lat: this.userLocation.lat + (Math.random() - 0.5) * 0.03,
        lng: this.userLocation.lng + (Math.random() - 0.5) * 0.03,
        address: 'Adresse de prise en charge...'
      },
      destination: {
        lat: this.userLocation.lat + (Math.random() - 0.2) * 0.05,
        lng: this.userLocation.lng + (Math.random() - 0.2) * 0.05,
        address: 'Adresse de destination...'
      },
      fare: Math.floor(5 + Math.random() * 20),
      distance: `${(1 + Math.random() * 10).toFixed(1)} km`,
      duration: `${Math.floor(5 + Math.random() * 30)} min`,
      requestedAt: new Date(),
      status: 'pending'
    };

    // Get addresses for pickup and destination
    this.getAddressFromCoords(request.pickup.lat, request.pickup.lng)
      .then(address => {
        request.pickup.address = address;
        this.updateRideRequestNotification(request);
      });
    
    this.getAddressFromCoords(request.destination.lat, request.destination.lng)
      .then(address => request.destination.address = address);

    this.rideRequests.unshift(request);
    this.showRideRequestNotification(request);
  }

  private showRideRequestNotification(request: RideRequest) {
    const notification: Notification = {
      id: `notif_${request.id}`,
      type: 'ride_request',
      title: 'Nouvelle course disponible',
      message: `${request.distance} - ${request.fare} DT - ${request.pickup.address}`,
      timestamp: new Date(),
      read: false,
      data: request
    };

    this.notifications.unshift(notification);
    
    // Show local notification for ride request
    this.showLocalNotification(
      '🎯 NOUVELLE COURSE', 
      `${request.passenger.name} - ${request.distance} - ${request.fare} DT\nTapotez pour voir les détails`
    );
    
    // Show alert for new ride request
    this.presentRideRequestAlert(request);
  }

  private updateRideRequestNotification(request: RideRequest) {
    const notificationIndex = this.notifications.findIndex(n => n.id === `notif_${request.id}`);
    if (notificationIndex > -1) {
      this.notifications[notificationIndex].message = `${request.distance} - ${request.fare} DT - ${request.pickup.address}`;
    }
  }

  async presentRideRequestAlert(request: RideRequest) {
    const alert = await this.alertController.create({
      header: '🎯 Nouvelle Course',
      message: `
        <strong>${request.passenger.name}</strong> ⭐ ${request.passenger.rating}<br>
        <strong>Distance:</strong> ${request.distance}<br>
        <strong>Prix:</strong> ${request.fare} DT<br>
        <strong>Durée:</strong> ${request.duration}<br>
        <strong>Prise en charge:</strong> ${request.pickup.address}
      `,
      buttons: [
        {
          text: 'Refuser',
          role: 'cancel',
          handler: () => {
            this.declineRide(request.id);
          }
        },
        {
          text: 'Accepter',
          handler: () => {
            this.acceptRide(request.id);
          }
        }
      ],
      backdropDismiss: false
    });
    
    await alert.present();
    
    // Auto-decline after 30 seconds
    setTimeout(() => {
      alert.dismiss().then(() => {
        if (request.status === 'pending') {
          this.declineRide(request.id);
        }
      });
    }, 30000);
  }

  acceptRide(rideId: string) {
    const ride = this.rideRequests.find(r => r.id === rideId);
    if (ride) {
      ride.status = 'accepted';
      this.currentRide = ride;
      this.driverStatus.busy = true;
      
      // Remove other pending requests
      this.rideRequests = this.rideRequests.filter(r => r.id === rideId || r.status !== 'pending');
      
      this.showRideDetails = true;
      this.showRouteOnMap();
      this.showNotification('Course acceptée', 'Rendez-vous au point de prise en charge');
      this.showLocalNotification('✅ Course Acceptée', `Direction: ${ride.pickup.address}\nPassager: ${ride.passenger.name}`);
    }
  }

  declineRide(rideId: string) {
    this.rideRequests = this.rideRequests.filter(r => r.id !== rideId);
    this.showNotification('Course refusée', '');
  }

  private showRouteOnMap() {
    if (!this.currentRide) return;

    // Clear existing route
    if (this.routePolyline) {
      this.map.removeLayer(this.routePolyline);
    }

    // Add pickup marker
    const pickupMarker = L.marker(
      [this.currentRide.pickup.lat, this.currentRide.pickup.lng],
      { icon: this.customIcons.pickup }
    ).addTo(this.map);

    pickupMarker.bindPopup(`
      <div class="pickup-popup">
        <strong>Point de prise en charge</strong><br>
        ${this.currentRide.pickup.address}<br>
        <strong>Passager:</strong> ${this.currentRide.passenger.name}
      </div>
    `).openPopup();

    // Add destination marker
    const destinationMarker = L.marker(
      [this.currentRide.destination.lat, this.currentRide.destination.lng],
      { icon: this.customIcons.destination }
    ).addTo(this.map);

    destinationMarker.bindPopup(`
      <div class="destination-popup">
        <strong>Destination</strong><br>
        ${this.currentRide.destination.address}
      </div>
    `);

    // Draw route (simplified)
    this.routePolyline = L.polyline([
      [this.userLocation.lat, this.userLocation.lng],
      [this.currentRide.pickup.lat, this.currentRide.pickup.lng],
      [this.currentRide.destination.lat, this.currentRide.destination.lng]
    ], {
      color: '#3880ff',
      weight: 4,
      opacity: 0.7,
      dashArray: '10, 10'
    }).addTo(this.map);

    // Fit map to show entire route
    const bounds = this.routePolyline.getBounds();
    this.map.fitBounds(bounds, { padding: [20, 20] });
  }

  async startRide() {
    if (!this.currentRide) return;

    this.showNotification('Course démarrée', 'Roulez vers la destination');
    this.showLocalNotification('🚗 Course Démarrée', `Direction: ${this.currentRide.destination.address}`);
    
    const alert = await this.alertController.create({
      header: 'Course démarrée',
      message: `Direction: ${this.currentRide.destination.address}`,
      buttons: ['OK']
    });
    await alert.present();
  }

  async completeRide() {
    if (!this.currentRide) return;

    // Update driver stats
    this.driverStatus.earning += this.currentRide.fare;
    this.driverStatus.completedRides += 1;

    const alert = await this.alertController.create({
      header: 'Course terminée',
      message: `
        <strong>Prix:</strong> ${this.currentRide.fare} DT<br>
        <strong>Total gagné aujourd'hui:</strong> ${this.driverStatus.earning} DT<br>
        <strong>Courses complétées:</strong> ${this.driverStatus.completedRides}
      `,
      buttons: [{
        text: 'OK',
        handler: () => {
          this.finishRide();
        }
      }]
    });
    await alert.present();
  }

  private finishRide() {
    if (this.currentRide) {
      this.currentRide.status = 'completed';
      
      // Add to notifications
      const paymentNotification: Notification = {
        id: `complete_${this.currentRide.id}`,
        type: 'payment',
        title: 'Paiement reçu',
        message: `${this.currentRide.fare} DT - ${this.currentRide.passenger.name}`,
        timestamp: new Date(),
        read: false
      };

      this.notifications.unshift(paymentNotification);
      this.showLocalNotification('💰 Paiement Reçu', `${this.currentRide.fare} DT de ${this.currentRide.passenger.name}`);
    }

    this.resetRide();
  }

  cancelCurrentRide() {
    if (this.currentRide) {
      this.currentRide.status = 'cancelled';
      this.showNotification('Course annulée', '');
      this.showLocalNotification('❌ Course Annulée', 'La course a été annulée');
    }
    this.resetRide();
  }

  private resetRide() {
    this.currentRide = null;
    this.driverStatus.busy = false;
    this.showRideDetails = false;
    
    // Clear map
    if (this.routePolyline) {
      this.map.removeLayer(this.routePolyline);
      this.routePolyline = null;
    }
    
    // Remove all markers except driver
    this.map.eachLayer(layer => {
      if (layer instanceof L.Marker && layer !== this.userMarker) {
        this.map.removeLayer(layer);
      }
    });

    // Center on driver
    this.centerOnDriver();
  }

  // Notification system
  private showNotification(title: string, message: string) {
    console.log('📢 In-app notification:', title, message);
  }

  markNotificationAsRead(notificationId: string) {
    const notification = this.notifications.find(n => n.id === notificationId);
    if (notification) {
      notification.read = true;
    }
  }

  clearAllNotifications() {
    this.notifications.forEach(notification => notification.read = true);
  }

  getNotificationIcon(type: string): string {
    const icons: { [key: string]: string } = {
      'ride_request': 'car-sport',
      'ride_cancelled': 'close-circle',
      'payment': 'cash',
      'system': 'information-circle'
    };
    return icons[type] || 'notifications';
  }

  getNotificationColor(type: string): string {
    const colors: { [key: string]: string } = {
      'ride_request': 'primary',
      'ride_cancelled': 'danger',
      'payment': 'success',
      'system': 'warning'
    };
    return colors[type] || 'medium';
  }

  // Geocoding functions
  private async makeCORSRequest(url: string): Promise<any> {
    try {
      const response = await fetch(url, {
        headers: {
          'User-Agent': 'TaxiDriverApp/1.0'
        }
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error('Geocoding request failed:', error);
      return { address: { road: 'Rue inconnue', city: 'Ville inconnue' } };
    }
  }

  private async getAddressFromCoords(lat: number, lng: number): Promise<string> {
    try {
      const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=fr`;
      const data = await this.makeCORSRequest(url);
      
      if (data.address) {
        const address = data.address;
        let displayName = '';
        
        if (address.road && address.house_number) {
          displayName = `${address.road} ${address.house_number}`;
        } else if (address.road) {
          displayName = address.road;
        } else if (address.suburb) {
          displayName = address.suburb;
        } else if (address.city) {
          displayName = address.city;
        } else {
          displayName = `Position (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
        }
        
        if (address.city) {
          displayName += `, ${address.city}`;
        }
        
        return displayName;
      }
    } catch (error) {
      console.error('Geocoding error:', error);
    }
    
    return `Position (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
  }

  centerOnDriver() {
    if (this.userLocation.lat !== 0 && this.userLocation.lng !== 0 && this.map) {
      const userLocation: L.LatLngTuple = [this.userLocation.lat, this.userLocation.lng];
      this.map.setView(userLocation, 16);
      this.map.invalidateSize();
    }
  }

  toggleNotifications() {
    this.showNotifications = !this.showNotifications;
  }

  toggleEarnings() {
    this.showEarnings = !this.showEarnings;
  }

  // Load driver data from storage
  private loadDriverData() {
    const savedData = localStorage.getItem('driverData');
    if (savedData) {
      const parsedData = JSON.parse(savedData);
      this.driverStatus = { ...this.driverStatus, ...parsedData };
      console.log('Driver data loaded:', this.driverStatus);
    }
  }

  // Save driver data to storage
  private saveDriverData() {
    localStorage.setItem('driverData', JSON.stringify(this.driverStatus));
    console.log('Driver data saved:', this.driverStatus);
  }

  private cleanupMap() {
    if (this.map) {
      this.map.remove();
      this.isMapInitialized = false;
    }
    if (this.watchId) {
      Geolocation.clearWatch({ id: this.watchId });
    }
    if (this.rideRequestInterval) {
      clearInterval(this.rideRequestInterval);
    }
  }

  private async showLocationError() {
    const alert = await this.alertController.create({
      header: 'Erreur de localisation',
      message: 'Impossible d\'obtenir votre position. Vérifiez les permissions de localisation.',
      buttons: [
        {
          text: 'OK',
          role: 'cancel'
        },
        {
          text: 'Réessayer',
          handler: () => {
            this.getCurrentLocation();
          }
        }
      ]
    });
    await alert.present();
  }

  profile() {
    this.router.navigate(['/profile']);
  }

  ionViewDidEnter() {
    if (this.map) {
      setTimeout(() => {
        this.map.invalidateSize();
      }, 300);
    }
  }

  ionViewWillLeave() {
    this.saveDriverData();
    if (this.watchId) {
      Geolocation.clearWatch({ id: this.watchId });
      this.watchId = null;
    }
  }
}