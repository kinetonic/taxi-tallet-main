import { Component, OnInit, AfterViewInit, OnDestroy, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { FormsModule } from '@angular/forms';
import { Geolocation } from '@capacitor/geolocation';
import { LoadingController, AlertController, Platform } from '@ionic/angular';
import { Router } from '@angular/router';
import * as L from 'leaflet';

interface Taxi {
  id: string;
  lat: number;
  lng: number;
  available: boolean;
  driverName: string;
  carModel: string;
  licensePlate: string;
  rating: number;
  eta?: number;
  marker?: L.Marker;
}

interface AutocompleteResult {
  display_name: string;
  lat: string;
  lon: string;
  address?: any;
}

@Component({
  selector: 'app-map-home',
  templateUrl: './map-home.page.html',
  styleUrls: ['./map-home.page.scss'],
  standalone: true,
  imports: [CommonModule, IonicModule, FormsModule]
})
export class MapHomePage implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('mapContainer', { static: false }) mapContainer!: ElementRef;

  private map!: L.Map;
  private userMarker!: L.Marker;
  private destinationMarker: L.Marker | null = null;
  private watchId: string | null = null;
  private isMapInitialized = false;
  private taxiInterval: any = null;
  private taxiMarkers: L.Marker[] = [];
  
  // Map data
  userLocation: { lat: number; lng: number } = { lat: 0, lng: 0 };
  currentAddress: string = 'Localisation en cours...';
  destinationAddress: string = '';
  
  // Taxi data
  nearbyTaxis: Taxi[] = [];
  selectedTaxi: Taxi | null = null;
  
  // Autocomplete
  showAutocomplete: boolean = false;
  autocompleteResults: AutocompleteResult[] = [];
  searchQuery: string = '';
  
  // Ride estimation
  rideEstimate: { distance: string; duration: string; price: string } = {
    distance: '--',
    duration: '--',
    price: '--'
  };

  selectedOption: string = 'taxi';
  findTaxi: boolean = false;
  isLoading: boolean = true;

  // Custom icons
  private customIcons = {
    user: L.divIcon({
      html: `
        <div class="custom-marker user-marker">
          <div class="pulse-ring"></div>
          <div class="marker-pin">
            <ion-icon name="person" style="font-size: 24px; color: white;"></ion-icon>
          </div>
        </div>
      `,
      iconSize: [50, 50],
      iconAnchor: [15, 42],
      className: 'user-location-marker'
    }),
    
    destination: L.divIcon({
      html: `
        <div class="custom-marker destination-marker">
          <div class="marker-pin">
            <ion-icon name="location" style="font-size: 24px; color: white;"></ion-icon>
          </div>
        </div>
      `,
      iconSize: [50, 50],
      iconAnchor: [15, 42],
      className: 'destination-location-marker'
    }),
    
    pickup: L.divIcon({
      html: `
        <div class="custom-marker pickup-marker">
          <div class="marker-pin">
            <ion-icon name="flag" style="font-size: 24px; color: white;"></ion-icon>
          </div>
        </div>
      `,
      iconSize: [50, 50],
      iconAnchor: [15, 42],
      className: 'pickup-location-marker'
    }),
    
    taxi: L.divIcon({
      html: `
        <div class="custom-marker taxi-marker">
          <div class="taxi-icon">
            <ion-icon name="car-sport" style="font-size: 24px; color: white;"></ion-icon>
          </div>
          <div class="taxi-pulse"></div>
        </div>
      `,
      iconSize: [50, 50],
      iconAnchor: [16, 16],
      className: 'taxi-location-marker'
    })
  };

  constructor(
    private loadingController: LoadingController,
    private alertController: AlertController,
    private router: Router,
    private platform: Platform
  ) { }

  async ngOnInit() {
    console.log('MapHomePage initialized');
    await this.platform.ready();
  }

  ngAfterViewInit() {
    // Wait for view to be ready
    setTimeout(() => {
      this.initMap();
    }, 300);
  }

  ngOnDestroy() {
    this.cleanupMap();
  }

  private initMap() {
    if (this.isMapInitialized) return;

    try {
      const defaultCoords: L.LatLngTuple = [36.8065, 10.1815]; // Tunis center
      
      this.map = L.map(this.mapContainer.nativeElement, {
        zoomControl: false,
        preferCanvas: true,
        doubleClickZoom: false
      }).setView(defaultCoords, 13);

      // Use OpenStreetMap tiles (free)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        minZoom: 3
      }).addTo(this.map);

      // Add zoom control
      L.control.zoom({
        position: 'topright'
      }).addTo(this.map);

      // Create user marker
      this.userMarker = L.marker(defaultCoords, { 
        icon: this.customIcons.user,
        zIndexOffset: 1000
      }).addTo(this.map);

      // Add click listener for destination
      this.map.on('click', (e: L.LeafletMouseEvent) => {
        this.placeDestinationMarker(e.latlng.lat, e.latlng.lng);
      });

      this.isMapInitialized = true;
      console.log('Leaflet map initialized successfully');

      // Get current location
      setTimeout(() => {
        this.getCurrentLocation();
      }, 500);

    } catch (error) {
      console.error('Error initializing map:', error);
      this.showMapError();
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
      this.startTaxiSimulation();

      await loading.dismiss();
      this.isLoading = false;

    } catch (error) {
      await loading.dismiss();
      this.isLoading = false;
      this.showLocationError();
      console.error('Error getting location:', error);
    }
  }

  private updateMapLocation(lat: number, lng: number) {
    if (!this.map) return;

    const newLocation: L.LatLngTuple = [lat, lng];
    this.map.setView(newLocation, 16);
    this.userMarker.setLatLng(newLocation);

    // Invalidate size to ensure proper rendering
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
          
          // Update address occasionally (not every time to avoid too many API calls)
          if (Math.random() < 0.1) { // 10% chance to update address
            this.currentAddress = await this.getAddressFromCoords(
              this.userLocation.lat, 
              this.userLocation.lng
            );
          }
        }
        
        if (err) {
          console.error('Error watching position:', err);
        }
      });

    } catch (error) {
      console.error('Error starting position watch:', error);
    }
  }

  // Simulate real-time taxis
  private startTaxiSimulation() {
    // Clear existing interval
    if (this.taxiInterval) {
      clearInterval(this.taxiInterval);
    }

    // Generate initial taxis
    this.generateNearbyTaxis();

    // Update taxis every 10 seconds
    this.taxiInterval = setInterval(() => {
      this.updateTaxiPositions();
    }, 10000);
  }

  private generateNearbyTaxis() {
    this.clearTaxiMarkers();
    this.nearbyTaxis = [];

    // Generate 5-10 random taxis around user
    const taxiCount = Math.floor(Math.random() * 6) + 5;
    
    for (let i = 0; i < taxiCount; i++) {
      const taxi: Taxi = {
        id: `taxi_${i}`,
        lat: this.userLocation.lat + (Math.random() - 0.5) * 0.02,
        lng: this.userLocation.lng + (Math.random() - 0.5) * 0.02,
        available: Math.random() > 0.3, // 70% available
        driverName: `Chauffeur ${i + 1}`,
        carModel: ['Toyota Corolla', 'Hyundai i10', 'Kia Picanto', 'Dacia Logan', 'Renault Clio'][i % 5],
        licensePlate: `TU ${Math.floor(1000 + Math.random() * 9000)} ABC`,
        rating: Number((3.5 + Math.random() * 1.5).toFixed(1)),
        eta: Math.floor(2 + Math.random() * 10)
      };

      this.nearbyTaxis.push(taxi);
      this.addTaxiToMap(taxi);
    }
  }

  private updateTaxiPositions() {
    this.nearbyTaxis.forEach(taxi => {
      // Move taxi randomly (simulate movement)
      taxi.lat += (Math.random() - 0.5) * 0.001;
      taxi.lng += (Math.random() - 0.5) * 0.001;
      
      // Update availability randomly
      if (Math.random() > 0.9) {
        taxi.available = !taxi.available;
      }

      // Update ETA
      taxi.eta = Math.floor(1 + Math.random() * 8);

      // Update marker on map
      this.updateTaxiMarker(taxi);
    });
  }

  private addTaxiToMap(taxi: Taxi) {
    const taxiLocation: L.LatLngTuple = [taxi.lat, taxi.lng];
    const taxiMarker = L.marker(taxiLocation, {
      icon: this.customIcons.taxi,
      zIndexOffset: 800
    }).addTo(this.map);

    // Add popup with taxi info
    taxiMarker.bindPopup(`
      <div class="taxi-popup" style="padding: 8px; font-family: Arial, sans-serif; max-width: 200px;">
        <strong style="color: #3880ff;">${taxi.driverName}</strong><br>
        <small>${taxi.carModel}</small><br>
        <small>${taxi.licensePlate}</small><br>
        ⭐ ${taxi.rating} | ${taxi.available ? '🟢 Disponible' : '🔴 Occupé'}<br>
        <small>Arrivée: ${taxi.eta} min</small>
      </div>
    `);

    // Add click event to select taxi
    taxiMarker.on('click', () => {
      if (taxi.available) {
        this.selectTaxi(taxi);
      }
    });

    taxi.marker = taxiMarker;
    this.taxiMarkers.push(taxiMarker);
  }

  private updateTaxiMarker(taxi: Taxi) {
    if (taxi.marker) {
      const newLocation: L.LatLngTuple = [taxi.lat, taxi.lng];
      taxi.marker.setLatLng(newLocation);

      // Update popup content
      taxi.marker.bindPopup(`
        <div class="taxi-popup" style="padding: 8px; font-family: Arial, sans-serif; max-width: 200px;">
          <strong style="color: #3880ff;">${taxi.driverName}</strong><br>
          <small>${taxi.carModel}</small><br>
          <small>${taxi.licensePlate}</small><br>
          ⭐ ${taxi.rating} | ${taxi.available ? '🟢 Disponible' : '🔴 Occupé'}<br>
          <small>Arrivée: ${taxi.eta} min</small>
        </div>
      `);
    }
  }

  private clearTaxiMarkers() {
    this.taxiMarkers.forEach(marker => {
      this.map.removeLayer(marker);
    });
    this.taxiMarkers = [];
  }

  selectTaxi(taxi: Taxi) {
    console.log('Taxi selected:', taxi);
    this.selectedTaxi = taxi;
    
    // Center map on selected taxi
    const taxiLocation: L.LatLngTuple = [taxi.lat, taxi.lng];
    this.map.setView(taxiLocation, 16);
  }

  // Geocoding using Nominatim (OpenStreetMap - free)
  private async getAddressFromCoords(lat: number, lng: number): Promise<string> {
    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
        {
          headers: {
            'User-Agent': 'TaxiApp/1.0',
            'Accept-Language': 'fr'
          }
        }
      );
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      
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
          displayName = data.display_name || `Position (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
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

  async searchAddress(event: any) {
    const query = event.detail.value || '';
    this.searchQuery = query;
    
    if (query.length < 3) {
      this.autocompleteResults = [];
      this.showAutocomplete = false;
      return;
    }

    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1&accept-language=fr`,
        {
          headers: {
            'User-Agent': 'TaxiApp/1.0'
          }
        }
      );
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const results = await response.json();
      this.autocompleteResults = results;
      this.showAutocomplete = true;
    } catch (error) {
      console.error('Autocomplete error:', error);
      this.autocompleteResults = [];
    }
  }

  // Select address from autocomplete
  async selectAddress(result: AutocompleteResult) {
    this.searchQuery = result.display_name;
    this.destinationAddress = result.display_name;
    this.showAutocomplete = false;
    
    this.placeDestinationMarker(parseFloat(result.lat), parseFloat(result.lon));
    
    const newLocation: L.LatLngTuple = [parseFloat(result.lat), parseFloat(result.lon)];
    this.map.setView(newLocation, 16);
    
    // Calculate ride estimate
    this.calculateRideEstimate();
    this.findTaxi = true;
  }

  searchAroundMe() {
    this.findTaxi = !this.findTaxi;
  }

  // Place destination marker
  private placeDestinationMarker(lat: number, lng: number) {
    if (this.destinationMarker) {
      this.map.removeLayer(this.destinationMarker);
    }

    const markerLocation: L.LatLngTuple = [lat, lng];
    this.destinationMarker = L.marker(markerLocation, {
      icon: this.customIcons.destination,
      draggable: true,
      zIndexOffset: 500
    }).addTo(this.map);

    this.destinationMarker.on('dragend', async (event: L.LeafletEvent) => {
      const marker = event.target as L.Marker;
      const position = marker.getLatLng();
      this.destinationAddress = await this.getAddressFromCoords(position.lat, position.lng);
      this.calculateRideEstimate();
    });

    this.getAddressFromCoords(lat, lng).then(address => {
      this.destinationAddress = address;
      this.calculateRideEstimate();
      
      this.destinationMarker?.bindPopup(`
        <div class="destination-popup" style="padding: 8px; font-family: Arial, sans-serif;">
          <strong style="color: #ff4961;">Destination</strong><br>
          <small>${this.destinationAddress}</small>
        </div>
      `);
    });
  }

  private calculateRideEstimate() {
    if (!this.destinationMarker) return;

    // Simulate distance calculation using Haversine formula
    const R = 6371; // Earth's radius in km
    const dLat = (this.destinationMarker.getLatLng().lat - this.userLocation.lat) * Math.PI / 180;
    const dLon = (this.destinationMarker.getLatLng().lng - this.userLocation.lng) * Math.PI / 180;
    const a = 
      Math.sin(dLat/2) * Math.sin(dLat/2) +
      Math.cos(this.userLocation.lat * Math.PI / 180) * Math.cos(this.destinationMarker.getLatLng().lat * Math.PI / 180) * 
      Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;
    
    // Simulate calculations
    const duration = Math.round((distance / 30) * 60); // 30 km/h average speed
    const price = Math.round(distance * 1.5 + 3); // 1.5 DT/km + 3 DT base
    
    this.rideEstimate = {
      distance: `${distance.toFixed(1)} km`,
      duration: `${duration} min`,
      price: `${price} DT`
    };
  }

  centerOnUser() {
    if (this.userLocation.lat !== 0 && this.userLocation.lng !== 0 && this.map) {
      const userLocation: L.LatLngTuple = [this.userLocation.lat, this.userLocation.lng];
      this.map.setView(userLocation, 16);
      this.map.invalidateSize();
    }
  }

  // Clear destination
  clearDestination() {
    if (this.destinationMarker) {
      this.map.removeLayer(this.destinationMarker);
      this.destinationMarker = null;
    }
    this.destinationAddress = '';
    this.searchQuery = '';
    this.showAutocomplete = false;
    this.autocompleteResults = [];
    this.rideEstimate = { distance: '--', duration: '--', price: '--' };
    this.selectedTaxi = null;
  }

  // Confirm ride
  async confirmRide() {
    if (!this.destinationAddress || !this.destinationMarker) {
      const alert = await this.alertController.create({
        header: 'Destination requise',
        message: 'Veuillez sélectionner une destination',
        buttons: ['OK']
      });
      await alert.present();
      return;
    }

    if (!this.selectedTaxi) {
      const alert = await this.alertController.create({
        header: 'Taxi non sélectionné',
        message: 'Veuillez sélectionner un taxi disponible',
        buttons: ['OK']
      });
      await alert.present();
      return;
    }

    const alert = await this.alertController.create({
      header: 'Confirmer la course',
      message: `
        <div style="text-align: left;">
          <strong>${this.selectedTaxi.driverName}</strong> arrive dans ${this.selectedTaxi.eta} minutes<br><br>
          <strong>Trajet:</strong><br>
          <small>📍 ${this.currentAddress}</small><br>
          <small>➡️ ${this.destinationAddress}</small><br><br>
          <strong>Prix estimé:</strong> ${this.rideEstimate.price}<br>
          <strong>Distance:</strong> ${this.rideEstimate.distance}<br>
          <strong>Durée:</strong> ${this.rideEstimate.duration}
        </div>
      `,
      buttons: [
        {
          text: 'Annuler',
          role: 'cancel'
        },
        {
          text: 'Confirmer',
          handler: () => {
            this.processRideConfirmation();
          }
        }
      ]
    });
    await alert.present();
  }

  private processRideConfirmation() {
    // Add pickup marker at user location
    const pickupLocation: L.LatLngTuple = [this.userLocation.lat, this.userLocation.lng];
    const pickupMarker = L.marker(pickupLocation, {
      icon: this.customIcons.pickup
    }).addTo(this.map);

    pickupMarker.bindPopup(`
      <div class="pickup-popup" style="padding: 8px; font-family: Arial, sans-serif;">
        <strong style="color: #ffce00;">Point de départ</strong><br>
        <small>${this.currentAddress}</small><br>
        <strong>Taxi:</strong> ${this.selectedTaxi?.driverName}
      </div>
    `).openPopup();

    // Draw route
    if (this.destinationMarker) {
      const destinationLatLng = this.destinationMarker.getLatLng();
      const polyline = L.polyline([
        pickupLocation,
        [destinationLatLng.lat, destinationLatLng.lng]
      ], {
        color: '#3880ff',
        weight: 4,
        opacity: 0.7
      }).addTo(this.map);
    }

    console.log('Ride confirmed:', {
      from: this.currentAddress,
      to: this.destinationAddress,
      taxi: this.selectedTaxi,
      estimate: this.rideEstimate
    });

    // Show success message
    this.showRideConfirmation();
  }

  private async showRideConfirmation() {
    const alert = await this.alertController.create({
      header: '🎉 Course réservée!',
      message: `Votre taxi ${this.selectedTaxi?.driverName} arrive dans ${this.selectedTaxi?.eta} minutes.`,
      buttons: [
        {
          text: 'Suivre la course',
          handler: () => {
            this.trackRide();
          }
        }
      ]
    });
    await alert.present();
  }

  private trackRide() {
    // Implement ride tracking logic
    console.log('Tracking ride...');
  }

  // Find available taxis
  findAvailableTaxis() {
    return this.nearbyTaxis.filter(taxi => taxi.available);
  }

  ionViewDidEnter() {
    if (this.map) {
      setTimeout(() => {
        this.map.invalidateSize();
      }, 300);
    }
  }

  async ionViewWillLeave() {
    if (this.watchId) {
      await Geolocation.clearWatch({ id: this.watchId });
      this.watchId = null;
    }
    if (this.taxiInterval) {
      clearInterval(this.taxiInterval);
    }
  }

  private cleanupMap() {
    if (this.map) {
      this.map.remove();
      this.isMapInitialized = false;
    }
    if (this.taxiInterval) {
      clearInterval(this.taxiInterval);
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

  private async showMapError() {
    const alert = await this.alertController.create({
      header: 'Erreur de carte',
      message: 'Impossible de charger la carte. Vérifiez votre connexion internet.',
      buttons: ['OK']
    });
    await alert.present();
  }

  profile() {
    this.router.navigate(['/profile']);
  }

  onBlur() {
    setTimeout(() => {
      this.showAutocomplete = false;
    }, 200);
  }

  onSegmentChange(event: any) {
    this.selectedOption = event.detail.value;
    console.log('Selected:', this.selectedOption);
  }
}