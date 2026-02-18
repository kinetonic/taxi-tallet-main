<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Course #{{ $trip->trip_id }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->created_at->format('d/m/Y à H:i') }} • 
                    <span class="font-medium">{{ $trip->distance }} km • {{ $trip->duration }} min</span>
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('trips.edit', $trip) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('trips.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Message -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Trip Details Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Détails de la course</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Trip Information -->
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Informations course</h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Statut:</span>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'searching' => 'bg-blue-100 text-blue-800',
                                                        'driver_assigned' => 'bg-indigo-100 text-indigo-800',
                                                        'driver_arrived' => 'bg-teal-100 text-teal-800',
                                                        'started' => 'bg-purple-100 text-purple-800',
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                    ];
                                                    
                                                    $statusLabels = [
                                                        'pending' => 'En attente',
                                                        'searching' => 'Recherche chauffeur',
                                                        'driver_assigned' => 'Chauffeur assigné',
                                                        'driver_arrived' => 'Chauffeur arrivé',
                                                        'started' => 'Course démarrée',
                                                        'completed' => 'Terminée',
                                                        'cancelled' => 'Annulée',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$trip->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $statusLabels[$trip->status] ?? $trip->status }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Type véhicule:</span>
                                                <span class="text-sm font-medium">{{ $trip->vehicle_type }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Distance:</span>
                                                <span class="text-sm font-medium">{{ $trip->distance }} km</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Durée estimée:</span>
                                                <span class="text-sm font-medium">{{ $trip->duration }} min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Information -->
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Informations paiement</h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Méthode:</span>
                                                <span class="text-sm font-medium">{{ $trip->payment_method }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Statut paiement:</span>
                                                <span class="text-sm font-medium {{ $trip->payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                                    {{ $trip->payment_status === 'paid' ? 'Payé' : ($trip->payment_status === 'pending' ? 'En attente' : ucfirst($trip->payment_status)) }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Prix estimé:</span>
                                                <span class="text-sm font-medium">{{ number_format($trip->estimated_price, 2) }} TND</span>
                                            </div>
                                            @if($trip->final_price)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-600">Prix final:</span>
                                                    <span class="text-sm font-medium text-green-600">{{ number_format($trip->final_price, 2) }} TND</span>
                                                </div>
                                            @endif
                                            @if($trip->driver_earnings)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-600">Gains chauffeur:</span>
                                                    <span class="text-sm font-medium">{{ number_format($trip->driver_earnings, 2) }} TND</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Information -->
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Point de départ</h4>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <div>
                                                    <p class="text-sm text-gray-900">{{ $trip->pickup_address }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Lat: {{ number_format($trip->pickup_latitude, 6) }}, 
                                                        Lng: {{ number_format($trip->pickup_longitude, 6) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Point d'arrivée</h4>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <div>
                                                    <p class="text-sm text-gray-900">{{ $trip->dropoff_address }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Lat: {{ number_format($trip->dropoff_latitude, 6) }}, 
                                                        Lng: {{ number_format($trip->dropoff_longitude, 6) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($trip->admin_notes)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500 mb-2">Notes administrateur</h4>
                                            <div class="bg-yellow-50 p-3 rounded-lg">
                                                <p class="text-sm text-gray-900">{{ $trip->admin_notes }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Chronologie de la course</h3>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($timeline as $index => $event)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex items-start space-x-3">
                                                    <div>
                                                        <div class="relative px-1">
                                                            <div class="h-8 w-8 {{ $event['color'] === 'green' ? 'bg-green-500' : ($event['color'] === 'blue' ? 'bg-blue-500' : ($event['color'] === 'red' ? 'bg-red-500' : ($event['color'] === 'yellow' ? 'bg-yellow-500' : ($event['color'] === 'purple' ? 'bg-purple-500' : 'bg-gray-500')))) }} rounded-full flex items-center justify-center ring-8 ring-white">
                                                                @if($event['icon'] === 'plus')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'search')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'user-friends')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0h-6"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'user-check')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'map-marker-alt')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'play')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'check-circle')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'credit-card')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                                    </svg>
                                                                @elseif($event['icon'] === 'times-circle')
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div>
                                                            <div class="text-sm">
                                                                <span class="font-medium text-gray-900">{{ $event['title'] }}</span>
                                                            </div>
                                                            <p class="mt-0.5 text-sm text-gray-500">{{ $event['description'] }}</p>
                                                        </div>
                                                        <div class="mt-2 text-sm text-gray-500">
                                                            <time datetime="{{ $event['time'] }}">{{ $event['time']->format('d/m/Y à H:i') }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Rider Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Client</h3>
                        </div>
                        <div class="p-6">
                            @if($trip->rider)
                                <div class="flex items-center">
                                    <div class="h-16 w-16 flex-shrink-0">
                                        <img class="h-16 w-16 rounded-full object-cover" src="{{ $trip->rider->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($trip->rider->full_name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ $trip->rider->full_name }}">
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-gray-900">{{ $trip->rider->full_name }}</h4>
                                        <div class="mt-1 space-y-1">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                {{ $trip->rider->phone }}
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $trip->rider->email }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-900">{{ $trip->rider->total_trips ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">Courses</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-900">{{ number_format($trip->rider->rider_rating ?? 0, 1) }}</div>
                                        <div class="text-xs text-gray-500">Note</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="#" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Voir profil
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0h-6"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Client non trouvé</h3>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Driver Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Chauffeur</h3>
                        </div>
                        <div class="p-6">
                            @if($trip->driver)
                                <div class="flex items-center">
                                    <div class="h-16 w-16 flex-shrink-0">
                                        <img class="h-16 w-16 rounded-full object-cover" src="{{ $trip->driver->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($trip->driver->full_name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ $trip->driver->full_name }}">
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-gray-900">{{ $trip->driver->full_name }}</h4>
                                        <div class="mt-1 space-y-1">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                {{ $trip->driver->phone }}
                                            </div>
                                            @if($trip->driver->vehicle_plate_number)
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                    </svg>
                                                    {{ $trip->driver->vehicle_plate_number }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-2">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-900">{{ $trip->driver->total_trips ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">Courses</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-900">{{ number_format($trip->driver->rating ?? 0, 1) }}</div>
                                        <div class="text-xs text-gray-500">Note</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-900">{{ number_format($trip->driver->earnings ?? 0, 0) }}</div>
                                        <div class="text-xs text-gray-500">TND</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="#" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Voir profil
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Chauffeur non assigné</h3>
                                    <p class="mt-1 text-sm text-gray-500">Cette course n'a pas encore de chauffeur.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('trips.edit', $trip) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Assigner un chauffeur
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ratings Card -->
                    @if($trip->driver_rating || $trip->rider_rating)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Évaluations</h3>
                            </div>
                            <div class="p-6">
                                @if($trip->driver_rating)
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Note du client</h4>
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $trip->driver_rating)
                                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="ml-2 text-sm font-medium">{{ number_format($trip->driver_rating, 1) }}/5</span>
                                        </div>
                                        @if($trip->driver_review)
                                            <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                                                <p class="text-sm text-gray-700 italic">"{{ $trip->driver_review }}"</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($trip->rider_rating)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Note du chauffeur</h4>
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $trip->rider_rating)
                                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="ml-2 text-sm font-medium">{{ number_format($trip->rider_rating, 1) }}/5</span>
                                        </div>
                                        @if($trip->rider_review)
                                            <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                                                <p class="text-sm text-gray-700 italic">"{{ $trip->rider_review }}"</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Similar Trips Card -->
                    @if($similarTrips->count() > 0)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Courses similaires</h3>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    @foreach($similarTrips as $similarTrip)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $similarTrip->trip_id }}</div>
                                                <div class="text-xs text-gray-500">{{ $similarTrip->created_at->format('d/m/Y') }}</div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $similarTrip->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $similarTrip->status === 'completed' ? 'Terminée' : ($similarTrip->status === 'cancelled' ? 'Annulée' : 'Active') }}
                                                </span>
                                                <a href="{{ route('trips.show', $similarTrip) }}" class="text-blue-600 hover:text-blue-900">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>