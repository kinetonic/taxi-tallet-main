import { Injectable } from '@angular/core';
import { Geolocation, Position } from '@capacitor/geolocation';
import * as L from 'leaflet';
//import 'leaflet/dist/leaflet.css';
import { BehaviorSubject } from 'rxjs';

// Fix for Leaflet default markers in web environments
const defaultIcon = L.icon({
  iconUrl: 'assets/leaflet/marker-icon.png',
  shadowUrl: 'assets/leaflet/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
});
L.Marker.prototype.options.icon = defaultIcon;

@Injectable({
  providedIn: 'root'
})
export class MapService {
  private map!: L.Map;
  private currentLocationMarker!: L.Marker;
  private currentLocationCircle!: L.Circle;
  private polylines: L.Polyline[] = [];
  private markers: L.Marker[] = [];
  
  // Observable for map readiness
  private mapReadySubject = new BehaviorSubject<boolean>(false);
  public mapReady$ = this.mapReadySubject.asObservable();

  // Default coordinates (fallback)
  private readonly DEFAULT_COORDINATES = {
    lat: 40.7128,
    lng: -74.0060 // New York City as fallback
  };

  // Map configuration
  private readonly MAP_CONFIG = {
    zoom: 15,
    minZoom: 3,
    maxZoom: 18,
    zoomControl: true,
    attributionControl: true
  };

  constructor() {}

  /**
   * Create and initialize the Leaflet map
   */
  async createMap(mapElement: HTMLElement, coordinates?: Position): Promise<void> {
    try {
      // Get initial coordinates if not provided or invalid
      if (!coordinates || !this.isValidCoordinates(coordinates)) {
        console.warn('Invalid coordinates provided, using default location');
        coordinates = await this.getDefaultPosition();
      }

      // Ensure coordinates are valid
      const validCoordinates = this.isValidCoordinates(coordinates) 
        ? coordinates 
        : await this.getDefaultPosition();

      // Initialize map
      this.map = L.map(mapElement, {
        ...this.MAP_CONFIG,
        center: [validCoordinates.coords.latitude, validCoordinates.coords.longitude],
        zoom: this.MAP_CONFIG.zoom
      });

      // Add tile layer (OpenStreetMap)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: this.MAP_CONFIG.maxZoom
      }).addTo(this.map);

      // Add current location marker
      await this.addCurrentLocationMarker(validCoordinates);

      this.mapReadySubject.next(true);
      
      console.log('Leaflet map initialized successfully');
    } catch (error) {
      console.error('Error initializing map:', error);
      
      // Fallback: Try to create map with default coordinates
      try {
        this.createMapWithDefault(mapElement);
      } catch (fallbackError) {
        console.error('Fallback map creation also failed:', fallbackError);
        throw new Error('Map initialization failed');
      }
    }
  }

  /**
   * Create map with default coordinates as fallback
   */
  private createMapWithDefault(mapElement: HTMLElement): void {
    this.map = L.map(mapElement, {
      ...this.MAP_CONFIG,
      center: [this.DEFAULT_COORDINATES.lat, this.DEFAULT_COORDINATES.lng],
      zoom: this.MAP_CONFIG.zoom
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: this.MAP_CONFIG.maxZoom
    }).addTo(this.map);

    this.mapReadySubject.next(true);
    console.log('Map initialized with default coordinates');
  }

  /**
   * Check if coordinates are valid
   */
  private isValidCoordinates(coordinates: Position): boolean {
    if (!coordinates || !coordinates.coords) return false;
    
    const lat = coordinates.coords.latitude;
    const lng = coordinates.coords.longitude;
    
    return !isNaN(lat) && 
           !isNaN(lng) && 
           lat >= -90 && 
           lat <= 90 && 
           lng >= -180 && 
           lng <= 180;
  }

  /**
   * Get default position as fallback
   */
  private async getDefaultPosition(): Promise<Position> {
    return {
      coords: {
        latitude: this.DEFAULT_COORDINATES.lat,
        longitude: this.DEFAULT_COORDINATES.lng,
        accuracy: 1000,
        altitude: null,
        altitudeAccuracy: null,
        heading: null,
        speed: null
      },
      timestamp: Date.now()
    };
  }

  /**
   * Get current position using Capacitor Geolocation
   */
  async getCurrentPosition(): Promise<Position> {
    try {
      // Check permissions
      const permissionStatus = await Geolocation.checkPermissions();
      
      if (permissionStatus.location !== 'granted') {
        const requestStatus = await Geolocation.requestPermissions();
        if (requestStatus.location !== 'granted') {
          throw new Error('Location permission denied');
        }
      }

      const position = await Geolocation.getCurrentPosition();
      
      if (!this.isValidCoordinates(position)) {
        throw new Error('Invalid coordinates received from geolocation');
      }

      return position;
    } catch (error) {
      console.error('Error getting current position:', error);
      // Return default position as fallback
      return this.getDefaultPosition();
    }
  }

  /**
   * Add current location marker to map
   */
  private async addCurrentLocationMarker(coordinates: Position): Promise<void> {
    if (!this.map || !this.isValidCoordinates(coordinates)) {
      console.warn('Cannot add current location marker: invalid coordinates or map not initialized');
      return;
    }

    const latLng: L.LatLngExpression = [coordinates.coords.latitude, coordinates.coords.longitude];

    // Remove existing marker and circle
    if (this.currentLocationMarker) {
      this.map.removeLayer(this.currentLocationMarker);
    }
    if (this.currentLocationCircle) {
      this.map.removeLayer(this.currentLocationCircle);
    }

    // Create custom location icon
    const locationIcon = L.divIcon({
      html: '<div class="current-location-pulse"></div>',
      className: 'current-location-marker',
      iconSize: [20, 20],
      iconAnchor: [10, 10]
    });

    // Add marker
    this.currentLocationMarker = L.marker(latLng, {
      icon: locationIcon,
      zIndexOffset: 1000
    }).addTo(this.map);

    // Add accuracy circle
    this.currentLocationCircle = L.circle(latLng, {
      color: '#007bff',
      fillColor: '#007bff',
      fillOpacity: 0.1,
      radius: coordinates.coords.accuracy || 1000
    }).addTo(this.map);
  }

  /**
   * Update current location on map
   */
  async updateCurrentLocation(coordinates: Position): Promise<void> {
    if (!this.map || !this.currentLocationMarker || !this.isValidCoordinates(coordinates)) {
      return;
    }

    const latLng: L.LatLngExpression = [coordinates.coords.latitude, coordinates.coords.longitude];

    // Update marker position
    this.currentLocationMarker.setLatLng(latLng);

    // Update accuracy circle
    if (this.currentLocationCircle) {
      this.currentLocationCircle.setLatLng(latLng);
      this.currentLocationCircle.setRadius(coordinates.coords.accuracy || 1000);
    }

    // Optionally pan to new location
    this.map.panTo(latLng);
  }

  /**
   * Set map center and zoom
   */
  setCamera(options: {
    coordinate: { lat: number; lng: number };
    zoom?: number;
    animate?: boolean;
    animationDuration?: number;
  }): void {
    if (!this.map || !this.isValidLatLng(options.coordinate)) return;

    const latLng: L.LatLngExpression = [options.coordinate.lat, options.coordinate.lng];
    
    if (options.animate) {
      this.map.flyTo(latLng, options.zoom || this.MAP_CONFIG.zoom, {
        duration: (options.animationDuration || 500) / 1000 // Convert to seconds
      });
    } else {
      this.map.setView(latLng, options.zoom || this.MAP_CONFIG.zoom);
    }
  }

  /**
   * Check if LatLng object is valid
   */
  private isValidLatLng(coordinate: { lat: number; lng: number }): boolean {
    return coordinate && 
           !isNaN(coordinate.lat) && 
           !isNaN(coordinate.lng) &&
           coordinate.lat >= -90 &&
           coordinate.lat <= 90 &&
           coordinate.lng >= -180 &&
           coordinate.lng <= 180;
  }

  /**
   * Fit map bounds to show multiple locations
   */
  fitBounds(locations: { lat: number; lng: number }[]): void {
    if (!this.map || !locations.length) return;

    // Filter out invalid locations
    const validLocations = locations.filter(loc => this.isValidLatLng(loc));
    
    if (validLocations.length === 0) return;

    const bounds = L.latLngBounds(validLocations.map(loc => [loc.lat, loc.lng] as L.LatLngTuple));
    this.map.fitBounds(bounds, { padding: [20, 20] });
  }

  /**
   * Add marker to map
   */
  addMarker(options: {
    coordinate: { lat: number; lng: number };
    title?: string;
    iconUrl?: string;
    iconSize?: { width: number; height: number };
    iconAnchor?: { x: number; y: number };
  }): string | null {
    if (!this.map || !this.isValidLatLng(options.coordinate)) {
      console.warn('Cannot add marker: invalid coordinates or map not initialized');
      return null;
    }

    const latLng: L.LatLngExpression = [options.coordinate.lat, options.coordinate.lng];
    
    let customIcon: L.Icon | L.DivIcon = defaultIcon;

    if (options.iconUrl) {
      customIcon = L.icon({
        iconUrl: options.iconUrl,
        iconSize: options.iconSize ? [options.iconSize.width, options.iconSize.height] : [30, 30],
        iconAnchor: options.iconAnchor ? [options.iconAnchor.x, options.iconAnchor.y] : [15, 30],
        popupAnchor: [0, -30]
      });
    }

    try {
      const marker = L.marker(latLng, {
        icon: customIcon,
        title: options.title
      }).addTo(this.map);

      const markerId = `marker_${Date.now()}`;
      this.markers.push(marker);

      return markerId;
    } catch (error) {
      console.error('Error adding marker:', error);
      return null;
    }
  }

  /**
   * Remove marker from map
   */
  removeMarker(markerId: string): void {
    if (!this.map) return;

    const markerIndex = this.markers.findIndex(marker => {
      //return marker['_leaflet_id'] === markerId;
    });

    if (markerIndex !== -1) {
      this.map.removeLayer(this.markers[markerIndex]);
      this.markers.splice(markerIndex, 1);
    }
  }

  /**
   * Add polyline to map
   */
  addPolyline(path: { lat: number; lng: number }[], color: string = '#007bff', weight: number = 6): string | null {
    if (!this.map || !path.length) return null;

    // Filter out invalid points
    const validPath = path.filter(point => this.isValidLatLng(point));
    
    if (validPath.length === 0) return null;

    const latLngs: L.LatLngExpression[] = validPath.map(point => [point.lat, point.lng] as L.LatLngTuple);
    
    try {
      const polyline = L.polyline(latLngs, {
        color: color,
        weight: weight,
        opacity: 0.8,
        lineJoin: 'round'
      }).addTo(this.map);

      const polylineId = `polyline_${Date.now()}`;
      this.polylines.push(polyline);

      return polylineId;
    } catch (error) {
      console.error('Error adding polyline:', error);
      return null;
    }
  }

  /**
   * Remove polyline from map
   */
  removePolyline(polylineId: string): void {
    if (!this.map) return;

    const polylineIndex = this.polylines.findIndex(polyline => {
      //return polyline['_leaflet_id'] === polylineId;
    });

    if (polylineIndex !== -1) {
      this.map.removeLayer(this.polylines[polylineIndex]);
      this.polylines.splice(polylineIndex, 1);
    }
  }

  /**
   * Clear all polylines from map
   */
  clearAllPolylines(): void {
    if (!this.map) return;
    
    this.polylines.forEach(polyline => {
      this.map.removeLayer(polyline);
    });
    this.polylines = [];
  }

  /**
   * Clear all markers (except current location)
   */
  clearAllMarkers(): void {
    if (!this.map) return;
    
    this.markers.forEach(marker => {
      this.map.removeLayer(marker);
    });
    this.markers = [];
  }

  /**
   * Calculate center point of multiple locations
   */
  calculateCenter(locations: { lat: number; lng: number }[]): { lat: number; lng: number } {
    const validLocations = locations.filter(loc => this.isValidLatLng(loc));
    
    if (!validLocations.length) return this.DEFAULT_COORDINATES;

    const sum = validLocations.reduce(
      (acc, loc) => {
        acc.lat += loc.lat;
        acc.lng += loc.lng;
        return acc;
      },
      { lat: 0, lng: 0 }
    );

    return {
      lat: sum.lat / validLocations.length,
      lng: sum.lng / validLocations.length
    };
  }

  /**
   * Calculate bearing between two points
   */
  calculateBearing(start: { lat: number; lng: number }, end: { lat: number; lng: number }): number {
    if (!this.isValidLatLng(start) || !this.isValidLatLng(end)) {
      return 0;
    }

    const startLat = this.toRadians(start.lat);
    const startLng = this.toRadians(start.lng);
    const endLat = this.toRadians(end.lat);
    const endLng = this.toRadians(end.lng);

    const y = Math.sin(endLng - startLng) * Math.cos(endLat);
    const x = Math.cos(startLat) * Math.sin(endLat) -
              Math.sin(startLat) * Math.cos(endLat) * Math.cos(endLng - startLng);
    
    let bearing = Math.atan2(y, x);
    bearing = this.toDegrees(bearing);
    bearing = (bearing + 360) % 360;

    return bearing;
  }

  /**
   * Get address from coordinates using reverse geocoding
   */
  async getAddress(lat: number, lng: number): Promise<any> {
    if (!this.isValidLatLng({ lat, lng })) {
      throw new Error('Invalid coordinates for reverse geocoding');
    }

    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
      );
      
      if (!response.ok) {
        throw new Error('Reverse geocoding failed');
      }
      
      return await response.json();
    } catch (error) {
      console.error('Error getting address:', error);
      throw error;
    }
  }

  /**
   * Enable/disable map interactions
   */
  enableTouch(enable: boolean = true): void {
    if (!this.map) return;

    if (enable) {
      this.map.dragging.enable();
      this.map.touchZoom.enable();
      this.map.doubleClickZoom.enable();
      this.map.scrollWheelZoom.enable();
    } else {
      this.map.dragging.disable();
      this.map.touchZoom.disable();
      this.map.doubleClickZoom.disable();
      this.map.scrollWheelZoom.disable();
    }
  }

  /**
   * Show/hide current location marker
   */
  enableCurrentLocation(enable: boolean = true): void {
    if (!this.map || !this.currentLocationMarker) return;

    if (enable) {
      this.map.addLayer(this.currentLocationMarker);
      if (this.currentLocationCircle) {
        this.map.addLayer(this.currentLocationCircle);
      }
    } else {
      this.map.removeLayer(this.currentLocationMarker);
      if (this.currentLocationCircle) {
        this.map.removeLayer(this.currentLocationCircle);
      }
    }
  }

  /**
   * Get map instance (for advanced operations)
   */
  getMapInstance(): L.Map | null {
    return this.map || null;
  }

  /**
   * Destroy map and clean up resources
   */
  destroyMap(): void {
    if (this.map) {
      this.map.remove();
      //this.map = null;
    }
    this.markers = [];
    this.polylines = [];
    this.mapReadySubject.next(false);
  }

  /**
   * Utility function to convert degrees to radians
   */
  private toRadians(degrees: number): number {
    return degrees * (Math.PI / 180);
  }

  /**
   * Utility function to convert radians to degrees
   */
  private toDegrees(radians: number): number {
    return radians * (180 / Math.PI);
  }
}