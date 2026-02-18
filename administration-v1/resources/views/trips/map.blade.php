{{-- resources/views/trips/map.blade.php --}}
@extends('layouts.app')

@section('title', 'Carte des Courses en Temps Réel')

@section('content')
<div class="container-fluid px-0">
    <!-- Map Header -->
    <div class="map-header bg-white shadow-sm py-3 px-4 border-bottom">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-map-marked-alt text-primary me-2"></i>
                    Carte des Courses en Temps Réel
                </h1>
                <p class="text-muted mb-0">
                    Surveillance des courses actives et chauffeurs disponibles
                </p>
            </div>
            <div class="col-auto">
                <div class="d-flex align-items-center gap-3">
                    <!-- Legend -->
                    <div class="d-flex align-items-center me-3">
                        <span class="legend-dot bg-danger me-1"></span>
                        <small class="text-muted">Course active</small>
                        <span class="legend-dot bg-success ms-3 me-1"></span>
                        <small class="text-muted">Chauffeur disponible</small>
                        <span class="legend-dot bg-info ms-3 me-1"></span>
                        <small class="text-muted">Destination</small>
                    </div>
                    
                    <!-- Refresh Button -->
                    <button id="refreshMap" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Actualiser
                    </button>
                    
                    <!-- Back Button -->
                    <a href="{{ route('trips.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="row g-0">
        <!-- Map -->
        <div class="col-12 col-lg-9">
            <div id="tripMap" style="height: calc(100vh - 140px);"></div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-3 border-start">
            <div class="sidebar-content" style="height: calc(100vh - 140px); overflow-y: auto;">
                <!-- Stats Cards -->
                <div class="p-3 border-bottom">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3 text-center">
                                    <h3 class="text-primary mb-1">{{ $activeTrips->count() }}</h3>
                                    <small class="text-muted">Courses actives</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3 text-center">
                                    <h3 class="text-success mb-1">{{ $availableDrivers->count() }}</h3>
                                    <small class="text-muted">Chauffeurs disponibles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Trips List -->
                <div class="p-3 border-bottom">
                    <h6 class="mb-3">
                        <i class="fas fa-car me-2 text-danger"></i>
                        Courses Actives
                        <span class="badge bg-danger ms-2">{{ $activeTrips->count() }}</span>
                    </h6>
                    
                    @if($activeTrips->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activeTrips as $trip)
                            <div class="list-group-item border-0 px-0 py-2" data-trip-id="{{ $trip->id }}" onclick="focusOnTrip('{{ $trip->id }}')">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-danger rounded-circle">
                                                <i class="fas fa-car"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">
                                            {{ $trip->trip_id }}
                                            <span class="badge bg-{{ $trip->status == 'started' ? 'success' : 'warning' }} float-end">
                                                {{ ucfirst($trip->status) }}
                                            </span>
                                        </h6>
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $trip->rider->full_name ?? 'N/A' }}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-road me-1"></i>
                                            {{ Str::limit($trip->pickup_address, 30) }}
                                        </small>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-arrow-right text-primary me-1"></i>
                                                {{ $trip->distance }} km
                                            </span>
                                            <span class="badge bg-light text-dark ms-2">
                                                <i class="fas fa-clock text-primary me-1"></i>
                                                {{ $trip->duration }} min
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-car text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">Aucune course active</p>
                        </div>
                    @endif
                </div>

                <!-- Available Drivers List -->
                <div class="p-3">
                    <h6 class="mb-3">
                        <i class="fas fa-user-check me-2 text-success"></i>
                        Chauffeurs Disponibles
                        <span class="badge bg-success ms-2">{{ $availableDrivers->count() }}</span>
                    </h6>
                    
                    @if($availableDrivers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($availableDrivers as $driver)
                            <div class="list-group-item border-0 px-0 py-2" data-driver-id="{{ $driver->id }}" onclick="focusOnDriver('{{ $driver->id }}')">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        @if($driver->profile_picture)
                                            <img src="{{ asset('storage/' . $driver->profile_picture) }}" 
                                                 alt="{{ $driver->full_name }}" 
                                                 class="rounded-circle avatar-xs">
                                        @else
                                            <div class="avatar-xs">
                                                <span class="avatar-title bg-success rounded-circle">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">{{ $driver->full_name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-star text-warning me-1"></i>
                                            {{ number_format($driver->rating, 1) }} 
                                            <i class="fas fa-car ms-2 me-1"></i>
                                            {{ $driver->vehicle_type ?? 'N/A' }}
                                        </small>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle fa-xs me-1"></i>
                                            En ligne
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">Aucun chauffeur disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .sidebar-content {
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
    }
    
    .sidebar-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-content::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .sidebar-content::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 3px;
    }
    
    .list-group-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .avatar-xs {
        width: 32px;
        height: 32px;
    }
    
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    
    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
</style>
@endpush

@push('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Heatmap Plugin -->
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
    let map;
    let tripMarkers = {};
    let driverMarkers = {};
    let destinationMarkers = {};
    let tripRoutes = {};

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        loadMapData();
        setupRefreshButton();
    });

    function initMap() {
        // Initialize map
        map = L.map('tripMap').setView([{{ $centerLat }}, {{ $centerLng }}], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        // Add heatmap layer for recent trips
        @if($recentTrips->count() > 0)
        let heatmapPoints = [];
        @foreach($recentTrips as $trip)
            @if($trip->pickup_latitude && $trip->pickup_longitude)
                heatmapPoints.push([{{ $trip->pickup_latitude }}, {{ $trip->pickup_longitude }}, 1]);
            @endif
        @endforeach
        
        if (heatmapPoints.length > 0) {
            L.heatLayer(heatmapPoints, {
                radius: 25,
                blur: 15,
                maxZoom: 17,
                gradient: {0.4: 'blue', 0.65: 'lime', 1: 'red'}
            }).addTo(map);
        }
        @endif
    }

    function loadMapData() {
        // Clear existing markers
        Object.values(tripMarkers).forEach(marker => map.removeLayer(marker));
        Object.values(driverMarkers).forEach(marker => map.removeLayer(marker));
        Object.values(destinationMarkers).forEach(marker => map.removeLayer(marker));
        Object.values(tripRoutes).forEach(route => map.removeLayer(route));

        // Add trip markers
        @foreach($activeTrips as $trip)
            @if($trip->pickup_latitude && $trip->pickup_longitude)
                let tripMarker = L.marker([{{ $trip->pickup_latitude }}, {{ $trip->pickup_longitude }}], {
                    icon: L.divIcon({
                        html: `<div class="trip-marker">
                                <i class="fas fa-car fa-lg text-danger"></i>
                                <span class="marker-badge">{{ $trip->trip_id }}</span>
                               </div>`,
                        className: 'custom-trip-marker',
                        iconSize: [40, 40],
                        iconAnchor: [20, 40]
                    })
                }).addTo(map);

                tripMarker.bindPopup(`
                    <div class="trip-popup">
                        <h6>Course {{ $trip->trip_id }}</h6>
                        <p><strong>Client:</strong> {{ $trip->rider->full_name ?? 'N/A' }}</p>
                        <p><strong>Statut:</strong> {{ ucfirst($trip->status) }}</p>
                        <p><strong>Départ:</strong> {{ Str::limit($trip->pickup_address, 50) }}</p>
                        <p><strong>Destination:</strong> {{ Str::limit($trip->dropoff_address, 50) }}</p>
                        <p><strong>Distance:</strong> {{ $trip->distance }} km</p>
                        <p><strong>Durée:</strong> {{ $trip->duration }} min</p>
                        <a href="{{ route('trips.show', $trip) }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-eye me-1"></i> Voir détails
                        </a>
                    </div>
                `);

                tripMarkers['{{ $trip->id }}'] = tripMarker;

                // Add destination marker if available
                @if($trip->dropoff_latitude && $trip->dropoff_longitude)
                    let destMarker = L.marker([{{ $trip->dropoff_latitude }}, {{ $trip->dropoff_longitude }}], {
                        icon: L.divIcon({
                            html: `<div class="destination-marker">
                                    <i class="fas fa-flag-checkered fa-lg text-info"></i>
                                   </div>`,
                            className: 'custom-destination-marker',
                            iconSize: [30, 30],
                            iconAnchor: [15, 30]
                        })
                    }).addTo(map);

                    destinationMarkers['{{ $trip->id }}'] = destMarker;

                    // Add route line
                    let routeLine = L.polyline([
                        [{{ $trip->pickup_latitude }}, {{ $trip->pickup_longitude }}],
                        [{{ $trip->dropoff_latitude }}, {{ $trip->dropoff_longitude }}]
                    ], {
                        color: '#007bff',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '10, 10'
                    }).addTo(map);

                    tripRoutes['{{ $trip->id }}'] = routeLine;
                @endif
            @endif
        @endforeach

        // Add driver markers
        @foreach($availableDrivers as $driver)
            @if($driver->current_latitude && $driver->current_longitude)
                let driverMarker = L.marker([{{ $driver->current_latitude }}, {{ $driver->current_longitude }}], {
                    icon: L.divIcon({
                        html: `<div class="driver-marker">
                                <i class="fas fa-user fa-lg text-success"></i>
                               </div>`,
                        className: 'custom-driver-marker',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    })
                }).addTo(map);

                driverMarker.bindPopup(`
                    <div class="driver-popup">
                        <h6>{{ $driver->full_name }}</h6>
                        <p><strong>Véhicule:</strong> {{ $driver->vehicle_type ?? 'N/A' }}</p>
                        <p><strong>Note:</strong> {{ number_format($driver->rating, 1) }}/5</p>
                        <p><strong>Statut:</strong> Disponible</p>
                        <p><strong>Téléphone:</strong> {{ $driver->phone ?? 'N/A' }}</p>
                        <a href="#" class="btn btn-sm btn-success mt-2">
                            <i class="fas fa-user me-1"></i> Voir profil
                        </a>
                    </div>
                `);

                driverMarkers['{{ $driver->id }}'] = driverMarker;
            @endif
        @endforeach
    }

    function focusOnTrip(tripId) {
        if (tripMarkers[tripId]) {
            map.setView(tripMarkers[tripId].getLatLng(), 15);
            tripMarkers[tripId].openPopup();
        }
    }

    function focusOnDriver(driverId) {
        if (driverMarkers[driverId]) {
            map.setView(driverMarkers[driverId].getLatLng(), 15);
            driverMarkers[driverId].openPopup();
        }
    }

    function setupRefreshButton() {
        document.getElementById('refreshMap').addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Actualisation...';
            btn.disabled = true;
            
            // Simulate refresh (in real app, you would fetch new data)
            setTimeout(() => {
                loadMapData();
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                
                // Show success message
                showToast('Carte actualisée avec succès', 'success');
            }, 1000);
        });
    }

    function showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function () {
            document.body.removeChild(toast);
        });
    }
</script>

<style>
    /* Custom marker styles */
    .custom-trip-marker .trip-marker {
        position: relative;
    }
    
    .custom-trip-marker .marker-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .custom-driver-marker .driver-marker i {
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .custom-destination-marker .destination-marker i {
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    /* Popup styles */
    .leaflet-popup-content {
        min-width: 250px;
    }
    
    .trip-popup, .driver-popup {
        font-size: 14px;
    }
    
    .trip-popup h6, .driver-popup h6 {
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }
    
    .trip-popup p, .driver-popup p {
        margin-bottom: 5px;
    }
</style>
@endpush