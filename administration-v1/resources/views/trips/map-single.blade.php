{{-- resources/views/trips/map-single.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Carte de la Course #{{ $trip->trip_id }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Trajet de {{ Str::limit($trip->pickup_address, 50) }} à {{ Str::limit($trip->dropoff_address, 50) }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('trips.show', $trip) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour aux détails
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-0">
        <div class="max-w-full mx-auto">
            <!-- Map Controls -->
            <div class="bg-white shadow-sm border-b border-gray-200 py-3 px-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                            <span class="text-sm text-gray-600">Départ</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                            <span class="text-sm text-gray-600">Arrivée</span>
                        </div>
                        @if($trip->driver)
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                <span class="text-sm text-gray-600">Position chauffeur</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Distance Info -->
                        <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">
                            <span class="text-sm font-medium">{{ $trip->distance }} km</span>
                        </div>
                        
                        <!-- Duration Info -->
                        <div class="bg-green-50 text-green-700 px-3 py-1 rounded-lg">
                            <span class="text-sm font-medium">{{ $trip->duration }} min</span>
                        </div>
                        
                        <!-- Price Info -->
                        @if($trip->final_price)
                            <div class="bg-purple-50 text-purple-700 px-3 py-1 rounded-lg">
                                <span class="text-sm font-medium">{{ number_format($trip->final_price, 2) }} TND</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="relative h-[calc(100vh-140px)]">
                <div id="tripMap" class="absolute inset-0"></div>
                
                <!-- Loading Overlay -->
                <div id="loadingOverlay" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center z-50">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Chargement de la carte...</p>
                    </div>
                </div>

                <!-- Side Panel -->
                <div class="absolute top-4 right-4 w-80 bg-white rounded-lg shadow-xl z-40 overflow-hidden">
                    <!-- Trip Information -->
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900 mb-2">Informations course</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Statut:</span>
                                <span class="text-sm font-medium {{ $trip->status === 'completed' ? 'text-green-600' : ($trip->status === 'cancelled' ? 'text-red-600' : 'text-yellow-600') }}">
                                    {{ $trip->status === 'completed' ? 'Terminée' : ($trip->status === 'cancelled' ? 'Annulée' : 'En cours') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Heure demande:</span>
                                <span class="text-sm font-medium">{{ $trip->created_at->format('H:i') }}</span>
                            </div>
                            @if($trip->started_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Heure départ:</span>
                                    <span class="text-sm font-medium">{{ $trip->started_at->format('H:i') }}</span>
                                </div>
                            @endif
                            @if($trip->completed_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Heure arrivée:</span>
                                    <span class="text-sm font-medium">{{ $trip->completed_at->format('H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Rider Information -->
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900 mb-3">Client</h3>
                        <div class="flex items-center">
                            @if($trip->rider && $trip->rider->profile_picture)
                                <img src="{{ asset('storage/' . $trip->rider->profile_picture) }}" 
                                     alt="{{ $trip->rider->name }}" 
                                     class="w-10 h-10 rounded-full mr-3">
                            @else
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $trip->rider->name ?? 'Client inconnu' }}</p>
                                @if($trip->rider)
                                    <p class="text-sm text-gray-500">{{ $trip->rider->phone ?? 'Téléphone non disponible' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Driver Information -->
                    @if($trip->driver)
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-3">Chauffeur</h3>
                            <div class="flex items-center">
                                @if($trip->driver->profile_picture)
                                    <img src="{{ asset('storage/' . $trip->driver->profile_picture) }}" 
                                         alt="{{ $trip->driver->name }}" 
                                         class="w-10 h-10 rounded-full mr-3">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $trip->driver->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $trip->driver->phone }}</p>
                                    @if($trip->driver->vehicle_plate_number)
                                        <p class="text-xs text-gray-500">{{ $trip->driver->vehicle_plate_number }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if($trip->driver->current_latitude && $trip->driver->current_longitude)
                                <button id="centerOnDriver" class="mt-3 w-full bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Centrer sur le chauffeur
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Address Details -->
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Adresses</h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div class="w-3 h-3 rounded-full bg-green-500 mt-1 mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Départ</p>
                                    <p class="text-xs text-gray-600">{{ $trip->pickup_address }}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-3 h-3 rounded-full bg-red-500 mt-1 mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Arrivée</p>
                                    <p class="text-xs text-gray-600">{{ $trip->dropoff_address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Controls -->
                <div class="absolute bottom-4 left-4 right-4 flex justify-center space-x-3 z-40">
                    <button id="zoomIn" class="bg-white p-3 rounded-full shadow-lg hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                        </svg>
                    </button>
                    <button id="zoomOut" class="bg-white p-3 rounded-full shadow-lg hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                        </svg>
                    </button>
                    <button id="fitBounds" class="bg-white p-3 rounded-full shadow-lg hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                        </svg>
                    </button>
                    <button id="toggleRoute" class="bg-white p-3 rounded-full shadow-lg hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <style>
        #tripMap {
            z-index: 1;
        }
        
        .leaflet-popup-content {
            min-width: 250px;
        }
        
        .driver-marker {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .route-popup {
            font-size: 14px;
        }
        
        .route-popup h4 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .legend-marker {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
        let map;
        let pickupMarker;
        let dropoffMarker;
        let driverMarker;
        let routeControl;
        let routeVisible = true;
        
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            setupControls();
        });
        
        function initMap() {
            // Calculate center point between pickup and dropoff
            const centerLat = ({{ $trip->pickup_latitude }} + {{ $trip->dropoff_latitude }}) / 2;
            const centerLng = ({{ $trip->pickup_longitude }} + {{ $trip->dropoff_longitude }}) / 2;
            
            // Initialize map
            map = L.map('tripMap').setView([centerLat, centerLng], 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);
            
            // Add pickup marker
            pickupMarker = L.marker([{{ $trip->pickup_latitude }}, {{ $trip->pickup_longitude }}], {
                icon: L.divIcon({
                    html: `<div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div class="mt-1 px-2 py-1 bg-white rounded shadow text-xs font-medium">Départ</div>
                           </div>`,
                    className: 'custom-pickup-marker',
                    iconSize: [60, 60],
                    iconAnchor: [30, 60]
                })
            }).addTo(map);
            
            pickupMarker.bindPopup(`
                <div class="route-popup">
                    <h4>Point de départ</h4>
                    <p><strong>Adresse:</strong> {{ $trip->pickup_address }}</p>
                    <p><strong>Client:</strong> {{ $trip->rider->name ?? 'Inconnu' }}</p>
                    <p><strong>Demandé à:</strong> {{ $trip->created_at->format('H:i') }}</p>
                    @if($trip->requested_at)
                        <p><strong>Confirmé à:</strong> {{ $trip->requested_at->format('H:i') }}</p>
                    @endif
                </div>
            `);
            
            // Add dropoff marker
            dropoffMarker = L.marker([{{ $trip->dropoff_latitude }}, {{ $trip->dropoff_longitude }}], {
                icon: L.divIcon({
                    html: `<div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="mt-1 px-2 py-1 bg-white rounded shadow text-xs font-medium">Arrivée</div>
                           </div>`,
                    className: 'custom-dropoff-marker',
                    iconSize: [60, 60],
                    iconAnchor: [30, 60]
                })
            }).addTo(map);
            
            dropoffMarker.bindPopup(`
                <div class="route-popup">
                    <h4>Point d'arrivée</h4>
                    <p><strong>Adresse:</strong> {{ $trip->dropoff_address }}</p>
                    <p><strong>Distance:</strong> {{ $trip->distance }} km</p>
                    <p><strong>Durée estimée:</strong> {{ $trip->duration }} min</p>
                    @if($trip->final_price)
                        <p><strong>Prix:</strong> {{ number_format($trip->final_price, 2) }} TND</p>
                    @endif
                    @if($trip->completed_at)
                        <p><strong>Terminé à:</strong> {{ $trip->completed_at->format('H:i') }}</p>
                    @endif
                </div>
            `);
            
            // Add driver marker if available
            @if($trip->driver && $trip->driver->current_latitude && $trip->driver->current_longitude)
                driverMarker = L.marker([{{ $trip->driver->current_latitude }}, {{ $trip->driver->current_longitude }}], {
                    icon: L.divIcon({
                        html: `<div class="flex flex-col items-center driver-marker">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="mt-1 px-2 py-1 bg-white rounded shadow text-xs font-medium">Chauffeur</div>
                               </div>`,
                        className: 'custom-driver-marker',
                        iconSize: [60, 60],
                        iconAnchor: [30, 60]
                    })
                }).addTo(map);
                
                driverMarker.bindPopup(`
                    <div class="route-popup">
                        <h4>Chauffeur</h4>
                        <p><strong>Nom:</strong> {{ $trip->driver->name }}</p>
                        <p><strong>Téléphone:</strong> {{ $trip->driver->phone }}</p>
                        @if($trip->driver->vehicle_plate_number)
                            <p><strong>Plaque:</strong> {{ $trip->driver->vehicle_plate_number }}</p>
                        @endif
                        @if($trip->driver->vehicle_model)
                            <p><strong>Véhicule:</strong> {{ $trip->driver->vehicle_model }}</p>
                        @endif
                        <p><strong>Position actuelle</strong></p>
                    </div>
                `);
            @endif
            
            // Add routing control
            routeControl = L.Routing.control({
                waypoints: [
                    L.latLng({{ $trip->pickup_latitude }}, {{ $trip->pickup_longitude }}),
                    L.latLng({{ $trip->dropoff_latitude }}, {{ $trip->dropoff_longitude }})
                ],
                routeWhileDragging: false,
                showAlternatives: false,
                fitSelectedRoutes: false,
                lineOptions: {
                    styles: [{color: '#3B82F6', weight: 5, opacity: 0.7}]
                },
                createMarker: function() { return null; } // Disable default markers
            }).addTo(map);
            
            // Hide loading overlay
            setTimeout(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
                
                // Fit bounds to show all markers
                fitMapToBounds();
            }, 1000);
        }
        
        function setupControls() {
            // Zoom In
            document.getElementById('zoomIn').addEventListener('click', function() {
                map.zoomIn();
            });
            
            // Zoom Out
            document.getElementById('zoomOut').addEventListener('click', function() {
                map.zoomOut();
            });
            
            // Fit bounds
            document.getElementById('fitBounds').addEventListener('click', function() {
                fitMapToBounds();
            });
            
            // Toggle route
            document.getElementById('toggleRoute').addEventListener('click', function() {
                if (routeVisible) {
                    routeControl.remove();
                    routeVisible = false;
                    this.innerHTML = `
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    `;
                } else {
                    routeControl.addTo(map);
                    routeVisible = true;
                    this.innerHTML = `
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    `;
                }
            });
            
            // Center on driver
            @if($trip->driver && $trip->driver->current_latitude && $trip->driver->current_longitude)
                document.getElementById('centerOnDriver').addEventListener('click', function() {
                    map.setView([{{ $trip->driver->current_latitude }}, {{ $trip->driver->current_longitude }}], 15);
                    driverMarker.openPopup();
                });
            @endif
        }
        
        function fitMapToBounds() {
            const bounds = L.latLngBounds([
                [{{ $trip->pickup_latitude }}, {{ $trip->pickup_longitude }}],
                [{{ $trip->dropoff_latitude }}, {{ $trip->dropoff_longitude }}]
            ]);
            
            @if($trip->driver && $trip->driver->current_latitude && $trip->driver->current_longitude)
                bounds.extend([{{ $trip->driver->current_latitude }}, {{ $trip->driver->current_longitude }}]);
            @endif
            
            map.fitBounds(bounds.pad(0.2));
        }
        
        // Simulate driver movement for demo purposes (remove in production)
        @if($trip->driver && $trip->status === 'started' && $trip->driver->current_latitude && $trip->driver->current_longitude)
            let driverMoving = false;
            
            function simulateDriverMovement() {
                if (driverMoving) return;
                driverMoving = true;
                
                const startLat = {{ $trip->pickup_latitude }};
                const startLng = {{ $trip->pickup_longitude }};
                const endLat = {{ $trip->dropoff_latitude }};
                const endLng = {{ $trip->dropoff_longitude }};
                
                let progress = 0;
                const interval = setInterval(() => {
                    if (progress >= 1) {
                        clearInterval(interval);
                        driverMoving = false;
                        return;
                    }
                    
                    progress += 0.01;
                    
                    const currentLat = startLat + (endLat - startLat) * progress;
                    const currentLng = startLng + (endLng - startLng) * progress;
                    
                    driverMarker.setLatLng([currentLat, currentLng]);
                }, 100);
            }
            
            // Start simulation after 3 seconds
            setTimeout(simulateDriverMovement, 3000);
        @endif
    </script>
    @endpush
</x-app-layout>