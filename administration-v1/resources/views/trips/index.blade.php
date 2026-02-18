<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Courses</h1>
            <div class="flex items-center space-x-2">
                <a href="http://127.0.0.1:8000/trips/1/single-map" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    Vue Carte
                </a>
                <a href="{{ route('trips.analytics') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Analytics
                </a>
                <a href="{{ route('trips.export', request()->all()) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exporter CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Courses</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Courses Terminées</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">En Cours</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['started'] + $stats['driver_assigned'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Revenu Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['revenue'], 2) }} TND</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="ID, adresse, nom..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Paiement</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" {{ request('payment_method') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Vehicle Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule</label>
                            <select name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @foreach($vehicleTypes as $value => $label)
                                    <option value="{{ $value }}" {{ request('vehicle_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('trips.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Réinitialiser
                        </a>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Appliquer les filtres
                        </button>
                    </div>
                </form>
            </div>

            <!-- Trips Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px 6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chauffeur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trajet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($trips as $trip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $trip->trip_id }}</div>
                                        <div class="text-xs text-gray-500">{{ $trip->vehicle_type }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($trip->rider)
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 flex-shrink-0">
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $trip->rider->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($trip->rider->full_name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ $trip->rider->full_name }}">
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $trip->rider->full_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $trip->rider->phone }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($trip->driver)
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 flex-shrink-0">
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $trip->driver->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($trip->driver->full_name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ $trip->driver->full_name }}">
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $trip->driver->full_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $trip->driver->phone }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">Non assigné</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <div class="truncate max-w-xs">{{ $trip->pickup_address }}</div>
                                            <svg class="w-4 h-4 inline-block mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                            </svg>
                                            <div class="truncate max-w-xs">{{ $trip->dropoff_address }}</div>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $trip->distance }} km • {{ $trip->duration }} min
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($trip->final_price)
                                            <div class="text-sm font-medium text-green-600">{{ number_format($trip->final_price, 2) }} TND</div>
                                            @if($trip->driver_earnings)
                                                <div class="text-xs text-gray-500">Chauffeur: {{ number_format($trip->driver_earnings, 2) }} TND</div>
                                            @endif
                                        @else
                                            <div class="text-sm font-medium text-gray-600">{{ number_format($trip->estimated_price, 2) }} TND</div>
                                            <div class="text-xs text-gray-400">estimé</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                                'searching' => 'Recherche',
                                                'driver_assigned' => 'Chauffeur assigné',
                                                'driver_arrived' => 'Chauffeur arrivé',
                                                'started' => 'En cours',
                                                'completed' => 'Terminée',
                                                'cancelled' => 'Annulée',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$trip->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$trip->status] ?? $trip->status }}
                                        </span>
                                        @if($trip->payment_status)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Paiement: 
                                                <span class="{{ $trip->payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                                    {{ $trip->payment_status === 'paid' ? 'Payé' : ($trip->payment_status === 'pending' ? 'En attente' : ucfirst($trip->payment_status)) }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $trip->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs">{{ $trip->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('trips.show', $trip) }}" class="text-blue-600 hover:text-blue-900" title="Voir détails">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('trips.edit', $trip) }}" class="text-green-600 hover:text-green-900" title="Modifier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            @if(in_array($trip->status, ['cancelled', 'completed']))
                                                <form action="{{ route('trips.destroy', $trip) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette course ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune course</h3>
                                        <p class="mt-1 text-sm text-gray-500">Aucune course trouvée avec les filtres actuels.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($trips->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $trips->withQueryString()->links() }}
                    </div>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques du jour</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Courses aujourd'hui</span>
                            <span class="text-sm font-medium">{{ $stats['today'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Courses ce mois</span>
                            <span class="text-sm font-medium">{{ $stats['this_month'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Taux de complétion</span>
                            <span class="text-sm font-medium">
                                @if($stats['total'] > 0)
                                    {{ round(($stats['completed'] / $stats['total']) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par statut</h3>
                    <div class="space-y-2">
                        @foreach(['completed', 'started', 'cancelled', 'pending'] as $status)
                            @if($stats[$status] > 0)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        @php
                                            $colors = [
                                                'completed' => 'bg-green-500',
                                                'started' => 'bg-purple-500',
                                                'cancelled' => 'bg-red-500',
                                                'pending' => 'bg-yellow-500',
                                            ];
                                        @endphp
                                        <div class="w-3 h-3 rounded-full {{ $colors[$status] ?? 'bg-gray-500' }} mr-2"></div>
                                        <span class="text-sm text-gray-600">{{ $statusLabels[$status] ?? $status }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium">{{ $stats[$status] }}</span>
                                        @if($stats['total'] > 0)
                                            <span class="text-xs text-gray-500">
                                                ({{ round(($stats[$status] / $stats['total']) * 100, 1) }}%)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par paiement</h3>
                    <div class="space-y-2">
                        @php
                            $paymentStats = DB::table('trips')
                                ->select('payment_method', DB::raw('count(*) as count'))
                                ->groupBy('payment_method')
                                ->get();
                        @endphp
                        @foreach($paymentStats as $payment)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @php
                                        $paymentColors = [
                                            'cash' => 'bg-gray-500',
                                            'card' => 'bg-blue-500',
                                            'wallet' => 'bg-green-500',
                                            'mobile_money' => 'bg-purple-500',
                                        ];
                                    @endphp
                                    <div class="w-3 h-3 rounded-full {{ $paymentColors[$payment->payment_method] ?? 'bg-gray-500' }} mr-2"></div>
                                    <span class="text-sm text-gray-600">{{ $paymentMethods[$payment->payment_method] ?? $payment->payment_method }}</span>
                                </div>
                                <span class="text-sm font-medium">{{ $payment->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>