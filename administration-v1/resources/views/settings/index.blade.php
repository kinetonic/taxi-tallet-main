<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Paramètres de l'Application</h1>
            <div class="flex items-center space-x-2">
                <button onclick="saveAllSettings()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Sauvegarder tous les paramètres
                </button>
                <button onclick="resetToDefaults()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Réinitialiser
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Settings Tabs -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    @foreach($categories as $categoryKey => $categoryName)
                        <a href="#{{ $categoryKey }}" 
                           class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $loop->first ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                           onclick="showTab('{{ $categoryKey }}')">
                            <span class="flex items-center">
                                @switch($categoryKey)
                                    @case('pricing')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @break
                                    @case('commission')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        @break
                                    @case('vehicles')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                        @break
                                    @case('drivers')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        @break
                                    @case('trips')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @break
                                    @case('payments')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        @break
                                    @case('notifications')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        @break
                                    @case('application')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        @break
                                    @case('safety')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        @break
                                    @case('geofencing')
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                        </svg>
                                        @break
                                @endswitch
                                {{ $categoryName }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- Settings Forms -->
            <form id="settingsForm" method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-8">
                    <!-- Tarification Tab -->
                    <div id="pricing-tab" class="tab-content">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Tarification</h3>
                                        <p class="mt-1 text-sm text-gray-500">Gérer les tarifs et les prix de l'application</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('pricing')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Tarification
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Tarif de base -->
                                    <div class="space-y-2">
                                        <label for="settings[base_fare]" class="block text-sm font-medium text-gray-700">
                                            Tarif de base
                                            <span class="text-xs text-gray-500 block mt-1">Prix minimum pour un trajet</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[base_fare]" name="settings[base_fare]" value="{{ old('settings.base_fare', $settings['pricing']->where('key', 'base_fare')->first()->value ?? '3.50') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Prix par km -->
                                    <div class="space-y-2">
                                        <label for="settings[cost_per_km]" class="block text-sm font-medium text-gray-700">
                                            Prix par km
                                            <span class="text-xs text-gray-500 block mt-1">Coût par kilomètre parcouru</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[cost_per_km]" name="settings[cost_per_km]" value="{{ old('settings.cost_per_km', $settings['pricing']->where('key', 'cost_per_km')->first()->value ?? '1.20') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT/km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Prix par minute -->
                                    <div class="space-y-2">
                                        <label for="settings[cost_per_minute]" class="block text-sm font-medium text-gray-700">
                                            Prix par minute
                                            <span class="text-xs text-gray-500 block mt-1">Coût par minute d'attente ou de trafic</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[cost_per_minute]" name="settings[cost_per_minute]" value="{{ old('settings.cost_per_minute', $settings['pricing']->where('key', 'cost_per_minute')->first()->value ?? '0.30') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT/min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tarif minimum -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_fare]" class="block text-sm font-medium text-gray-700">
                                            Tarif minimum
                                            <span class="text-xs text-gray-500 block mt-1">Montant minimum pour un trajet</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[minimum_fare]" name="settings[minimum_fare]" value="{{ old('settings.minimum_fare', $settings['pricing']->where('key', 'minimum_fare')->first()->value ?? '5.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Majoration nuit -->
                                    <div class="space-y-2">
                                        <label for="settings[night_surcharge_percentage]" class="block text-sm font-medium text-gray-700">
                                            Majoration nuit (%)
                                            <span class="text-xs text-gray-500 block mt-1">Pourcentage supplémentaire pour les trajets de nuit (22h-6h)</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[night_surcharge_percentage]" name="settings[night_surcharge_percentage]" value="{{ old('settings.night_surcharge_percentage', $settings['pricing']->where('key', 'night_surcharge_percentage')->first()->value ?? '20') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Majoration heures pointe -->
                                    <div class="space-y-2">
                                        <label for="settings[peak_hours_surcharge_percentage]" class="block text-sm font-medium text-gray-700">
                                            Majoration heures pointe (%)
                                            <span class="text-xs text-gray-500 block mt-1">Pourcentage supplémentaire pendant les heures de pointe (7h-9h, 17h-19h)</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[peak_hours_surcharge_percentage]" name="settings[peak_hours_surcharge_percentage]" value="{{ old('settings.peak_hours_surcharge_percentage', $settings['pricing']->where('key', 'peak_hours_surcharge_percentage')->first()->value ?? '15') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Supplément aéroport -->
                                    <div class="space-y-2">
                                        <label for="settings[airport_surcharge]" class="block text-sm font-medium text-gray-700">
                                            Supplément aéroport
                                            <span class="text-xs text-gray-500 block mt-1">Supplément pour les trajets vers/depuis l'aéroport</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[airport_surcharge]" name="settings[airport_surcharge]" value="{{ old('settings.airport_surcharge', $settings['pricing']->where('key', 'airport_surcharge')->first()->value ?? '2.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Frais de réservation -->
                                    <div class="space-y-2">
                                        <label for="settings[booking_fee]" class="block text-sm font-medium text-gray-700">
                                            Frais de réservation
                                            <span class="text-xs text-gray-500 block mt-1">Frais fixes pour chaque réservation</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[booking_fee]" name="settings[booking_fee]" value="{{ old('settings.booking_fee', $settings['pricing']->where('key', 'booking_fee')->first()->value ?? '1.50') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Distance minimum -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_distance]" class="block text-sm font-medium text-gray-700">
                                            Distance minimum
                                            <span class="text-xs text-gray-500 block mt-1">Distance minimum facturée en km</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[minimum_distance]" name="settings[minimum_distance]" value="{{ old('settings.minimum_distance', $settings['pricing']->where('key', 'minimum_distance')->first()->value ?? '2.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Temps minimum -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_time]" class="block text-sm font-medium text-gray-700">
                                            Temps minimum
                                            <span class="text-xs text-gray-500 block mt-1">Temps minimum facturé en minutes</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[minimum_time]" name="settings[minimum_time]" value="{{ old('settings.minimum_time', $settings['pricing']->where('key', 'minimum_time')->first()->value ?? '5') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Taxe de service -->
                                    <div class="space-y-2">
                                        <label for="settings[service_tax_percentage]" class="block text-sm font-medium text-gray-700">
                                            Taxe de service (%)
                                            <span class="text-xs text-gray-500 block mt-1">Taxe de service appliquée sur chaque course</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[service_tax_percentage]" name="settings[service_tax_percentage]" value="{{ old('settings.service_tax_percentage', $settings['pricing']->where('key', 'service_tax_percentage')->first()->value ?? '5.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Frais de bagages -->
                                    <div class="space-y-2">
                                        <label for="settings[luggage_fee]" class="block text-sm font-medium text-gray-700">
                                            Frais de bagages
                                            <span class="text-xs text-gray-500 block mt-1">Frais supplémentaires par bagage</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[luggage_fee]" name="settings[luggage_fee]" value="{{ old('settings.luggage_fee', $settings['pricing']->where('key', 'luggage_fee')->first()->value ?? '1.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commissions Tab -->
                    <div id="commission-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Commissions</h3>
                                        <p class="mt-1 text-sm text-gray-500">Configurer les commissions et les frais</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('commission')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Commissions
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Commission chauffeur -->
                                    <div class="space-y-2">
                                        <label for="settings[driver_commission_percentage]" class="block text-sm font-medium text-gray-700">
                                            Commission chauffeur (%)
                                            <span class="text-xs text-gray-500 block mt-1">Pourcentage que le chauffeur reçoit</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[driver_commission_percentage]" name="settings[driver_commission_percentage]" value="{{ old('settings.driver_commission_percentage', $settings['commission']->where('key', 'driver_commission_percentage')->first()->value ?? '80') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Commission plateforme -->
                                    <div class="space-y-2">
                                        <label for="settings[platform_commission_percentage]" class="block text-sm font-medium text-gray-700">
                                            Commission plateforme (%)
                                            <span class="text-xs text-gray-500 block mt-1">Pourcentage que la plateforme garde</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[platform_commission_percentage]" name="settings[platform_commission_percentage]" value="{{ old('settings.platform_commission_percentage', $settings['commission']->where('key', 'platform_commission_percentage')->first()->value ?? '20') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Frais annulation (chauffeur) -->
                                    <div class="space-y-2">
                                        <label for="settings[cancellation_fee_driver]" class="block text-sm font-medium text-gray-700">
                                            Frais annulation (chauffeur)
                                            <span class="text-xs text-gray-500 block mt-1">Frais quand le chauffeur annule</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[cancellation_fee_driver]" name="settings[cancellation_fee_driver]" value="{{ old('settings.cancellation_fee_driver', $settings['commission']->where('key', 'cancellation_fee_driver')->first()->value ?? '5.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Frais annulation (client) -->
                                    <div class="space-y-2">
                                        <label for="settings[cancellation_fee_rider]" class="block text-sm font-medium text-gray-700">
                                            Frais annulation (client)
                                            <span class="text-xs text-gray-500 block mt-1">Frais quand le client annule</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[cancellation_fee_rider]" name="settings[cancellation_fee_rider]" value="{{ old('settings.cancellation_fee_rider', $settings['commission']->where('key', 'cancellation_fee_rider')->first()->value ?? '3.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Frais de transaction -->
                                    <div class="space-y-2">
                                        <label for="settings[transaction_fee_percentage]" class="block text-sm font-medium text-gray-700">
                                            Frais de transaction (%)
                                            <span class="text-xs text-gray-500 block mt-1">Frais de transaction bancaire/PayPal</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[transaction_fee_percentage]" name="settings[transaction_fee_percentage]" value="{{ old('settings.transaction_fee_percentage', $settings['commission']->where('key', 'transaction_fee_percentage')->first()->value ?? '2.9') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Frais fixes transaction -->
                                    <div class="space-y-2">
                                        <label for="settings[transaction_fee_fixed]" class="block text-sm font-medium text-gray-700">
                                            Frais fixes transaction
                                            <span class="text-xs text-gray-500 block mt-1">Frais fixes par transaction</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[transaction_fee_fixed]" name="settings[transaction_fee_fixed]" value="{{ old('settings.transaction_fee_fixed', $settings['commission']->where('key', 'transaction_fee_fixed')->first()->value ?? '0.30') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Minimum commission -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_commission]" class="block text-sm font-medium text-gray-700">
                                            Commission minimum
                                            <span class="text-xs text-gray-500 block mt-1">Commission minimum garantie au chauffeur</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[minimum_commission]" name="settings[minimum_commission]" value="{{ old('settings.minimum_commission', $settings['commission']->where('key', 'minimum_commission')->first()->value ?? '2.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Commission referral chauffeur -->
                                    <div class="space-y-2">
                                        <label for="settings[driver_referral_bonus]" class="block text-sm font-medium text-gray-700">
                                            Bonus référence chauffeur
                                            <span class="text-xs text-gray-500 block mt-1">Bonus pour chaque chauffeur référencé</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[driver_referral_bonus]" name="settings[driver_referral_bonus]" value="{{ old('settings.driver_referral_bonus', $settings['commission']->where('key', 'driver_referral_bonus')->first()->value ?? '50.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Commission referral client -->
                                    <div class="space-y-2">
                                        <label for="settings[rider_referral_bonus]" class="block text-sm font-medium text-gray-700">
                                            Bonus référence client
                                            <span class="text-xs text-gray-500 block mt-1">Bonus pour chaque client référencé</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[rider_referral_bonus]" name="settings[rider_referral_bonus]" value="{{ old('settings.rider_referral_bonus', $settings['commission']->where('key', 'rider_referral_bonus')->first()->value ?? '10.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Véhicules Tab -->
                    <div id="vehicles-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Véhicules</h3>
                                        <p class="mt-1 text-sm text-gray-500">Paramètres des véhicules et des catégories</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('vehicles')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Véhicules
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Types de véhicules -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Types de véhicules
                                            <span class="text-xs text-gray-500 block mt-1">Types de véhicules disponibles</span>
                                        </label>
                                        <div class="space-y-2">
                                            @php
                                                $vehicleTypes = ['standard', 'comfort', 'premium', 'van', 'electric', 'motorcycle'];
                                                $currentTypes = json_decode($settings['vehicles']->where('key', 'vehicle_types')->first()->value ?? '["standard","comfort","premium","van"]', true);
                                            @endphp
                                            @foreach($vehicleTypes as $type)
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="settings[vehicle_types][{{ $type }}]" name="settings[vehicle_types][]" value="{{ $type }}" {{ in_array($type, $currentTypes) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <label for="settings[vehicle_types][{{ $type }}]" class="ml-2 text-sm text-gray-900 capitalize">{{ $type }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Multiplicateur Standard -->
                                    <div class="space-y-2">
                                        <label for="settings[standard_multiplier]" class="block text-sm font-medium text-gray-700">
                                            Multiplicateur Standard
                                            <span class="text-xs text-gray-500 block mt-1">Multiplicateur de prix pour les véhicules standard</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[standard_multiplier]" name="settings[standard_multiplier]" value="{{ old('settings.standard_multiplier', $settings['vehicles']->where('key', 'standard_multiplier')->first()->value ?? '1.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Multiplicateur Confort -->
                                    <div class="space-y-2">
                                        <label for="settings[comfort_multiplier]" class="block text-sm font-medium text-gray-700">
                                            Multiplicateur Confort
                                            <span class="text-xs text-gray-500 block mt-1">Multiplicateur de prix pour les véhicules confort</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[comfort_multiplier]" name="settings[comfort_multiplier]" value="{{ old('settings.comfort_multiplier', $settings['vehicles']->where('key', 'comfort_multiplier')->first()->value ?? '1.3') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Multiplicateur Premium -->
                                    <div class="space-y-2">
                                        <label for="settings[premium_multiplier]" class="block text-sm font-medium text-gray-700">
                                            Multiplicateur Premium
                                            <span class="text-xs text-gray-500 block mt-1">Multiplicateur de prix pour les véhicules premium</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[premium_multiplier]" name="settings[premium_multiplier]" value="{{ old('settings.premium_multiplier', $settings['vehicles']->where('key', 'premium_multiplier')->first()->value ?? '1.8') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Multiplicateur Van -->
                                    <div class="space-y-2">
                                        <label for="settings[van_multiplier]" class="block text-sm font-medium text-gray-700">
                                            Multiplicateur Van
                                            <span class="text-xs text-gray-500 block mt-1">Multiplicateur de prix pour les vans</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[van_multiplier]" name="settings[van_multiplier]" value="{{ old('settings.van_multiplier', $settings['vehicles']->where('key', 'van_multiplier')->first()->value ?? '2.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Multiplicateur Électrique -->
                                    <div class="space-y-2">
                                        <label for="settings[electric_multiplier]" class="block text-sm font-medium text-gray-700">
                                            Multiplicateur Électrique
                                            <span class="text-xs text-gray-500 block mt-1">Multiplicateur de prix pour les véhicules électriques</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[electric_multiplier]" name="settings[electric_multiplier]" value="{{ old('settings.electric_multiplier', $settings['vehicles']->where('key', 'electric_multiplier')->first()->value ?? '1.1') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Multiplicateur Moto -->
                                    <div class="space-y-2">
                                        <label for="settings[motorcycle_multiplier]" class="block text-sm font-medium text-gray-700">
                                            Multiplicateur Moto
                                            <span class="text-xs text-gray-500 block mt-1">Multiplicateur de prix pour les motos</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[motorcycle_multiplier]" name="settings[motorcycle_multiplier]" value="{{ old('settings.motorcycle_multiplier', $settings['vehicles']->where('key', 'motorcycle_multiplier')->first()->value ?? '0.8') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Âge maximum véhicule -->
                                    <div class="space-y-2">
                                        <label for="settings[max_vehicle_age_years]" class="block text-sm font-medium text-gray-700">
                                            Âge maximum véhicule (années)
                                            <span class="text-xs text-gray-500 block mt-1">Âge maximum autorisé pour les véhicules</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_vehicle_age_years]" name="settings[max_vehicle_age_years]" value="{{ old('settings.max_vehicle_age_years', $settings['vehicles']->where('key', 'max_vehicle_age_years')->first()->value ?? '10') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Nombre maximum passagers -->
                                    <div class="space-y-2">
                                        <label for="settings[max_passengers_standard]" class="block text-sm font-medium text-gray-700">
                                            Passagers maximum (Standard)
                                            <span class="text-xs text-gray-500 block mt-1">Nombre maximum de passagers pour standard</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_passengers_standard]" name="settings[max_passengers_standard]" value="{{ old('settings.max_passengers_standard', $settings['vehicles']->where('key', 'max_passengers_standard')->first()->value ?? '4') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Nombre maximum passagers van -->
                                    <div class="space-y-2">
                                        <label for="settings[max_passengers_van]" class="block text-sm font-medium text-gray-700">
                                            Passagers maximum (Van)
                                            <span class="text-xs text-gray-500 block mt-1">Nombre maximum de passagers pour van</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_passengers_van]" name="settings[max_passengers_van]" value="{{ old('settings.max_passengers_van', $settings['vehicles']->where('key', 'max_passengers_van')->first()->value ?? '8') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chauffeurs Tab -->
                    <div id="drivers-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Chauffeurs</h3>
                                        <p class="mt-1 text-sm text-gray-500">Configuration des chauffeurs</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('drivers')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Chauffeurs
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Approval chauffeur requis -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Approval chauffeur requis
                                            <span class="text-xs text-gray-500 block mt-1">Les chauffeurs doivent être approuvés manuellement</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[driver_approval_required]" value="false">
                                            <input type="checkbox" id="settings[driver_approval_required]" name="settings[driver_approval_required]" value="true" {{ ($settings['drivers']->where('key', 'driver_approval_required')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Note minimum chauffeur -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_driver_rating]" class="block text-sm font-medium text-gray-700">
                                            Note minimum chauffeur
                                            <span class="text-xs text-gray-500 block mt-1">Note minimum pour rester actif sur la plateforme</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" min="0" max="5" id="settings[minimum_driver_rating]" name="settings[minimum_driver_rating]" value="{{ old('settings.minimum_driver_rating', $settings['drivers']->where('key', 'minimum_driver_rating')->first()->value ?? '4.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Limite heures en ligne -->
                                    <div class="space-y-2">
                                        <label for="settings[driver_online_hours_limit]" class="block text-sm font-medium text-gray-700">
                                            Limite heures en ligne
                                            <span class="text-xs text-gray-500 block mt-1">Nombre maximum d'heures continues en ligne</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[driver_online_hours_limit]" name="settings[driver_online_hours_limit]" value="{{ old('settings.driver_online_hours_limit', $settings['drivers']->where('key', 'driver_online_hours_limit')->first()->value ?? '12') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">heures</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Jours avant paiement -->
                                    <div class="space-y-2">
                                        <label for="settings[driver_commission_payout_days]" class="block text-sm font-medium text-gray-700">
                                            Jours avant paiement
                                            <span class="text-xs text-gray-500 block mt-1">Nombre de jours avant le paiement des commissions</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[driver_commission_payout_days]" name="settings[driver_commission_payout_days]" value="{{ old('settings.driver_commission_payout_days', $settings['drivers']->where('key', 'driver_commission_payout_days')->first()->value ?? '7') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">jours</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Âge minimum chauffeur -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_driver_age]" class="block text-sm font-medium text-gray-700">
                                            Âge minimum chauffeur
                                            <span class="text-xs text-gray-500 block mt-1">Âge minimum requis pour être chauffeur</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[minimum_driver_age]" name="settings[minimum_driver_age]" value="{{ old('settings.minimum_driver_age', $settings['drivers']->where('key', 'minimum_driver_age')->first()->value ?? '21') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">ans</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Expérience minimum conduite -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_driving_experience]" class="block text-sm font-medium text-gray-700">
                                            Expérience minimum conduite
                                            <span class="text-xs text-gray-500 block mt-1">Années d'expérience minimum requise</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[minimum_driving_experience]" name="settings[minimum_driving_experience]" value="{{ old('settings.minimum_driving_experience', $settings['drivers']->where('key', 'minimum_driving_experience')->first()->value ?? '2') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">ans</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Distance maximum pour accepter course -->
                                    <div class="space-y-2">
                                        <label for="settings[max_accept_distance_km]" class="block text-sm font-medium text-gray-700">
                                            Distance max acceptation (km)
                                            <span class="text-xs text-gray-500 block mt-1">Distance maximum pour accepter une course</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[max_accept_distance_km]" name="settings[max_accept_distance_km]" value="{{ old('settings.max_accept_distance_km', $settings['drivers']->where('key', 'max_accept_distance_km')->first()->value ?? '5.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Temps de réponse maximum -->
                                    <div class="space-y-2">
                                        <label for="settings[max_response_time_seconds]" class="block text-sm font-medium text-gray-700">
                                            Temps réponse max (sec)
                                            <span class="text-xs text-gray-500 block mt-1">Temps maximum pour accepter une course</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_response_time_seconds]" name="settings[max_response_time_seconds]" value="{{ old('settings.max_response_time_seconds', $settings['drivers']->where('key', 'max_response_time_seconds')->first()->value ?? '30') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">sec</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pourcentage acceptation minimum -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_acceptance_rate]" class="block text-sm font-medium text-gray-700">
                                            Taux acceptation minimum (%)
                                            <span class="text-xs text-gray-500 block mt-1">Taux d'acceptation minimum requis</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[minimum_acceptance_rate]" name="settings[minimum_acceptance_rate]" value="{{ old('settings.minimum_acceptance_rate', $settings['drivers']->where('key', 'minimum_acceptance_rate')->first()->value ?? '85') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pourcentage annulation maximum -->
                                    <div class="space-y-2">
                                        <label for="settings[maximum_cancellation_rate]" class="block text-sm font-medium text-gray-700">
                                            Taux annulation maximum (%)
                                            <span class="text-xs text-gray-500 block mt-1">Taux d'annulation maximum autorisé</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[maximum_cancellation_rate]" name="settings[maximum_cancellation_rate]" value="{{ old('settings.maximum_cancellation_rate', $settings['drivers']->where('key', 'maximum_cancellation_rate')->first()->value ?? '10') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Tab -->
                    <div id="trips-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Courses</h3>
                                        <p class="mt-1 text-sm text-gray-500">Paramètres des courses</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('trips')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Courses
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Temps d'attente maximum -->
                                    <div class="space-y-2">
                                        <label for="settings[max_waiting_time_minutes]" class="block text-sm font-medium text-gray-700">
                                            Temps d'attente maximum (minutes)
                                            <span class="text-xs text-gray-500 block mt-1">Temps d'attente maximum gratuit</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_waiting_time_minutes]" name="settings[max_waiting_time_minutes]" value="{{ old('settings.max_waiting_time_minutes', $settings['trips']->where('key', 'max_waiting_time_minutes')->first()->value ?? '5') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rayon de recherche -->
                                    <div class="space-y-2">
                                        <label for="settings[search_radius_km]" class="block text-sm font-medium text-gray-700">
                                            Rayon de recherche (km)
                                            <span class="text-xs text-gray-500 block mt-1">Rayon pour trouver les chauffeurs disponibles</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[search_radius_km]" name="settings[search_radius_km]" value="{{ old('settings.search_radius_km', $settings['trips']->where('key', 'search_radius_km')->first()->value ?? '5') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fenêtre annulation gratuite -->
                                    <div class="space-y-2">
                                        <label for="settings[trip_cancellation_window_minutes]" class="block text-sm font-medium text-gray-700">
                                            Fenêtre annulation gratuite (minutes)
                                            <span class="text-xs text-gray-500 block mt-1">Temps pour annuler gratuitement après acceptation</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[trip_cancellation_window_minutes]" name="settings[trip_cancellation_window_minutes]" value="{{ old('settings.trip_cancellation_window_minutes', $settings['trips']->where('key', 'trip_cancellation_window_minutes')->first()->value ?? '2') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Réservation à l'avance -->
                                    <div class="space-y-2">
                                        <label for="settings[advance_booking_hours]" class="block text-sm font-medium text-gray-700">
                                            Réservation à l'avance (heures)
                                            <span class="text-xs text-gray-500 block mt-1">Nombre d'heures maximum pour réserver à l'avance</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[advance_booking_hours]" name="settings[advance_booking_hours]" value="{{ old('settings.advance_booking_hours', $settings['trips']->where('key', 'advance_booking_hours')->first()->value ?? '24') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">heures</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Distance maximum course -->
                                    <div class="space-y-2">
                                        <label for="settings[max_trip_distance_km]" class="block text-sm font-medium text-gray-700">
                                            Distance maximum course (km)
                                            <span class="text-xs text-gray-500 block mt-1">Distance maximum autorisée pour une course</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_trip_distance_km]" name="settings[max_trip_distance_km]" value="{{ old('settings.max_trip_distance_km', $settings['trips']->where('key', 'max_trip_distance_km')->first()->value ?? '100') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Durée maximum course -->
                                    <div class="space-y-2">
                                        <label for="settings[max_trip_duration_minutes]" class="block text-sm font-medium text-gray-700">
                                            Durée maximum course (minutes)
                                            <span class="text-xs text-gray-500 block mt-1">Durée maximum autorisée pour une course</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_trip_duration_minutes]" name="settings[max_trip_duration_minutes]" value="{{ old('settings.max_trip_duration_minutes', $settings['trips']->where('key', 'max_trip_duration_minutes')->first()->value ?? '180') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nombre maximum arrêts -->
                                    <div class="space-y-2">
                                        <label for="settings[max_stops_per_trip]" class="block text-sm font-medium text-gray-700">
                                            Nombre maximum arrêts
                                            <span class="text-xs text-gray-500 block mt-1">Nombre maximum d'arrêts autorisés par course</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[max_stops_per_trip]" name="settings[max_stops_per_trip]" value="{{ old('settings.max_stops_per_trip', $settings['trips']->where('key', 'max_stops_per_trip')->first()->value ?? '3') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Frais arrêt supplémentaire -->
                                    <div class="space-y-2">
                                        <label for="settings[additional_stop_fee]" class="block text-sm font-medium text-gray-700">
                                            Frais arrêt supplémentaire
                                            <span class="text-xs text-gray-500 block mt-1">Frais pour chaque arrêt supplémentaire</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[additional_stop_fee]" name="settings[additional_stop_fee]" value="{{ old('settings.additional_stop_fee', $settings['trips']->where('key', 'additional_stop_fee')->first()->value ?? '2.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Estimation temps trajet -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Estimation temps trajet
                                            <span class="text-xs text-gray-500 block mt-1">Afficher l'estimation du temps de trajet</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[show_trip_estimate]" value="false">
                                            <input type="checkbox" id="settings[show_trip_estimate]" name="settings[show_trip_estimate]" value="true" {{ ($settings['trips']->where('key', 'show_trip_estimate')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Partage trajet -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Partage trajet
                                            <span class="text-xs text-gray-500 block mt-1">Autoriser le partage de trajet</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[enable_ride_sharing]" value="false">
                                            <input type="checkbox" id="settings[enable_ride_sharing]" name="settings[enable_ride_sharing]" value="true" {{ ($settings['trips']->where('key', 'enable_ride_sharing')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paiements Tab -->
                    <div id="payments-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Paiements</h3>
                                        <p class="mt-1 text-sm text-gray-500">Configuration des paiements</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('payments')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Paiements
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Méthodes de paiement -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Méthodes de paiement
                                            <span class="text-xs text-gray-500 block mt-1">Méthodes de paiement acceptées</span>
                                        </label>
                                        <div class="space-y-2">
                                            @php
                                                $paymentMethods = ['cash', 'card', 'mobile_money', 'paypal', 'bank_transfer'];
                                                $currentMethods = json_decode($settings['payments']->where('key', 'payment_methods')->first()->value ?? '["cash","card","mobile_money","paypal"]', true);
                                            @endphp
                                            @foreach($paymentMethods as $method)
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="settings[payment_methods][{{ $method }}]" name="settings[payment_methods][]" value="{{ $method }}" {{ in_array($method, $currentMethods) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <label for="settings[payment_methods][{{ $method }}]" class="ml-2 text-sm text-gray-900 capitalize">
                                                        @switch($method)
                                                            @case('cash') Espèces @break
                                                            @case('card') Carte @break
                                                            @case('mobile_money') Mobile Money @break
                                                            @case('paypal') PayPal @break
                                                            @case('bank_transfer') Virement bancaire @break
                                                        @endswitch
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Solde minimum portefeuille -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_wallet_balance]" class="block text-sm font-medium text-gray-700">
                                            Solde minimum portefeuille
                                            <span class="text-xs text-gray-500 block mt-1">Solde minimum requis dans le portefeuille</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[minimum_wallet_balance]" name="settings[minimum_wallet_balance]" value="{{ old('settings.minimum_wallet_balance', $settings['payments']->where('key', 'minimum_wallet_balance')->first()->value ?? '10.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Seuil paiement chauffeur -->
                                    <div class="space-y-2">
                                        <label for="settings[driver_payout_threshold]" class="block text-sm font-medium text-gray-700">
                                            Seuil paiement chauffeur
                                            <span class="text-xs text-gray-500 block mt-1">Montant minimum pour demander un paiement</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[driver_payout_threshold]" name="settings[driver_payout_threshold]" value="{{ old('settings.driver_payout_threshold', $settings['payments']->where('key', 'driver_payout_threshold')->first()->value ?? '50.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Devise -->
                                    <div class="space-y-2">
                                        <label for="settings[currency]" class="block text-sm font-medium text-gray-700">
                                            Devise
                                            <span class="text-xs text-gray-500 block mt-1">Devise principale de l'application</span>
                                        </label>
                                        <select id="settings[currency]" name="settings[currency]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                            @php
                                                $currencies = ['EUR' => 'Euro (DT)', 'USD' => 'Dollar ($)', 'GBP' => 'Livre (£)', 'XOF' => 'Franc CFA (CFA)', 'MAD' => 'Dirham marocain (DH)'];
                                                $currentCurrency = $settings['payments']->where('key', 'currency')->first()->value ?? 'EUR';
                                            @endphp
                                            @foreach($currencies as $code => $name)
                                                <option value="{{ $code }}" {{ $currentCurrency == $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Taux de change manuel -->
                                    <div class="space-y-2">
                                        <label for="settings[exchange_rate_usd]" class="block text-sm font-medium text-gray-700">
                                            Taux de change USD
                                            <span class="text-xs text-gray-500 block mt-1">Taux de change USD vers devise locale</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.0001" id="settings[exchange_rate_usd]" name="settings[exchange_rate_usd]" value="{{ old('settings.exchange_rate_usd', $settings['payments']->where('key', 'exchange_rate_usd')->first()->value ?? '0.85') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Frais retrait -->
                                    <div class="space-y-2">
                                        <label for="settings[withdrawal_fee]" class="block text-sm font-medium text-gray-700">
                                            Frais retrait
                                            <span class="text-xs text-gray-500 block mt-1">Frais pour chaque retrait d'argent</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[withdrawal_fee]" name="settings[withdrawal_fee]" value="{{ old('settings.withdrawal_fee', $settings['payments']->where('key', 'withdrawal_fee')->first()->value ?? '1.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pourcentage retrait minimum -->
                                    <div class="space-y-2">
                                        <label for="settings[withdrawal_minimum_percentage]" class="block text-sm font-medium text-gray-700">
                                            Retrait minimum (%)
                                            <span class="text-xs text-gray-500 block mt-1">Pourcentage minimum pour retrait</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[withdrawal_minimum_percentage]" name="settings[withdrawal_minimum_percentage]" value="{{ old('settings.withdrawal_minimum_percentage', $settings['payments']->where('key', 'withdrawal_minimum_percentage')->first()->value ?? '50') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Paiement automatique -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Paiement automatique
                                            <span class="text-xs text-gray-500 block mt-1">Activer les paiements automatiques</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[auto_payment_enabled]" value="false">
                                            <input type="checkbox" id="settings[auto_payment_enabled]" name="settings[auto_payment_enabled]" value="true" {{ ($settings['payments']->where('key', 'auto_payment_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Forcer pré-paiement -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Forcer pré-paiement
                                            <span class="text-xs text-gray-500 block mt-1">Forcer le pré-paiement pour toutes les courses</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[force_prepayment]" value="false">
                                            <input type="checkbox" id="settings[force_prepayment]" name="settings[force_prepayment]" value="true" {{ ($settings['payments']->where('key', 'force_prepayment')->first()->value ?? 'false') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="notifications-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                                        <p class="mt-1 text-sm text-gray-500">Paramètres des notifications</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('notifications')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Notifications
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Notifications SMS -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notifications SMS
                                            <span class="text-xs text-gray-500 block mt-1">Activer les notifications SMS</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[send_sms_notifications]" value="false">
                                            <input type="checkbox" id="settings[send_sms_notifications]" name="settings[send_sms_notifications]" value="true" {{ ($settings['notifications']->where('key', 'send_sms_notifications')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notifications Email -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notifications Email
                                            <span class="text-xs text-gray-500 block mt-1">Activer les notifications email</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[send_email_notifications]" value="false">
                                            <input type="checkbox" id="settings[send_email_notifications]" name="settings[send_email_notifications]" value="true" {{ ($settings['notifications']->where('key', 'send_email_notifications')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notifications Push -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notifications Push
                                            <span class="text-xs text-gray-500 block mt-1">Activer les notifications push</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[send_push_notifications]" value="false">
                                            <input type="checkbox" id="settings[send_push_notifications]" name="settings[send_push_notifications]" value="true" {{ ($settings['notifications']->where('key', 'send_push_notifications')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notifications chauffeur -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notifications chauffeur
                                            <span class="text-xs text-gray-500 block mt-1">Activer les notifications pour les chauffeurs</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[driver_notifications_enabled]" value="false">
                                            <input type="checkbox" id="settings[driver_notifications_enabled]" name="settings[driver_notifications_enabled]" value="true" {{ ($settings['notifications']->where('key', 'driver_notifications_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notifications client -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notifications client
                                            <span class="text-xs text-gray-500 block mt-1">Activer les notifications pour les clients</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[rider_notifications_enabled]" value="false">
                                            <input type="checkbox" id="settings[rider_notifications_enabled]" name="settings[rider_notifications_enabled]" value="true" {{ ($settings['notifications']->where('key', 'rider_notifications_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notification nouvelle course -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notification nouvelle course
                                            <span class="text-xs text-gray-500 block mt-1">Notifier pour les nouvelles courses</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[new_trip_notification]" value="false">
                                            <input type="checkbox" id="settings[new_trip_notification]" name="settings[new_trip_notification]" value="true" {{ ($settings['notifications']->where('key', 'new_trip_notification')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notification annulation -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notification annulation
                                            <span class="text-xs text-gray-500 block mt-1">Notifier pour les annulations</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[cancellation_notification]" value="false">
                                            <input type="checkbox" id="settings[cancellation_notification]" name="settings[cancellation_notification]" value="true" {{ ($settings['notifications']->where('key', 'cancellation_notification')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notification paiement -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notification paiement
                                            <span class="text-xs text-gray-500 block mt-1">Notifier pour les paiements</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[payment_notification]" value="false">
                                            <input type="checkbox" id="settings[payment_notification]" name="settings[payment_notification]" value="true" {{ ($settings['notifications']->where('key', 'payment_notification')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Notification promotion -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Notification promotion
                                            <span class="text-xs text-gray-500 block mt-1">Notifier pour les promotions</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[promotion_notification]" value="false">
                                            <input type="checkbox" id="settings[promotion_notification]" name="settings[promotion_notification]" value="true" {{ ($settings['notifications']->where('key', 'promotion_notification')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Fréquence notifications -->
                                    <div class="space-y-2">
                                        <label for="settings[notification_frequency_hours]" class="block text-sm font-medium text-gray-700">
                                            Fréquence notifications (heures)
                                            <span class="text-xs text-gray-500 block mt-1">Fréquence minimum entre notifications</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[notification_frequency_hours]" name="settings[notification_frequency_hours]" value="{{ old('settings.notification_frequency_hours', $settings['notifications']->where('key', 'notification_frequency_hours')->first()->value ?? '1') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">heures</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Tab -->
                    <div id="application-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Application</h3>
                                        <p class="mt-1 text-sm text-gray-500">Paramètres généraux de l'application</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('application')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Application
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Nom de l'application -->
                                    <div class="space-y-2">
                                        <label for="settings[app_name]" class="block text-sm font-medium text-gray-700">
                                            Nom de l'application
                                            <span class="text-xs text-gray-500 block mt-1">Nom affiché dans l'application</span>
                                        </label>
                                        <input type="text" id="settings[app_name]" name="settings[app_name]" value="{{ old('settings.app_name', $settings['application']->where('key', 'app_name')->first()->value ?? 'TaxiApp Pro') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>

                                    <!-- Version de l'application -->
                                    <div class="space-y-2">
                                        <label for="settings[app_version]" class="block text-sm font-medium text-gray-700">
                                            Version de l'application
                                            <span class="text-xs text-gray-500 block mt-1">Version actuelle de l'application</span>
                                        </label>
                                        <input type="text" id="settings[app_version]" name="settings[app_version]" value="{{ old('settings.app_version', $settings['application']->where('key', 'app_version')->first()->value ?? '1.0.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>

                                    <!-- Email support -->
                                    <div class="space-y-2">
                                        <label for="settings[support_email]" class="block text-sm font-medium text-gray-700">
                                            Email support
                                            <span class="text-xs text-gray-500 block mt-1">Email du support client</span>
                                        </label>
                                        <input type="email" id="settings[support_email]" name="settings[support_email]" value="{{ old('settings.support_email', $settings['application']->where('key', 'support_email')->first()->value ?? 'support@taxiapp.com') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>

                                    <!-- Téléphone support -->
                                    <div class="space-y-2">
                                        <label for="settings[support_phone]" class="block text-sm font-medium text-gray-700">
                                            Téléphone support
                                            <span class="text-xs text-gray-500 block mt-1">Numéro de téléphone du support</span>
                                        </label>
                                        <input type="text" id="settings[support_phone]" name="settings[support_phone]" value="{{ old('settings.support_phone', $settings['application']->where('key', 'support_phone')->first()->value ?? '+33123456789') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>

                                    <!-- Mode maintenance -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Mode maintenance
                                            <span class="text-xs text-gray-500 block mt-1">Mettre l'application en mode maintenance</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[maintenance_mode]" value="false">
                                            <input type="checkbox" id="settings[maintenance_mode]" name="settings[maintenance_mode]" value="true" {{ ($settings['application']->where('key', 'maintenance_mode')->first()->value ?? 'false') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Debug mode -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Mode debug
                                            <span class="text-xs text-gray-500 block mt-1">Activer le mode debug</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[debug_mode]" value="false">
                                            <input type="checkbox" id="settings[debug_mode]" name="settings[debug_mode]" value="true" {{ ($settings['application']->where('key', 'debug_mode')->first()->value ?? 'false') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Limite requêtes API -->
                                    <div class="space-y-2">
                                        <label for="settings[api_rate_limit]" class="block text-sm font-medium text-gray-700">
                                            Limite requêtes API
                                            <span class="text-xs text-gray-500 block mt-1">Nombre maximum de requêtes par minute</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[api_rate_limit]" name="settings[api_rate_limit]" value="{{ old('settings.api_rate_limit', $settings['application']->where('key', 'api_rate_limit')->first()->value ?? '60') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Cache durée -->
                                    <div class="space-y-2">
                                        <label for="settings[cache_duration_minutes]" class="block text-sm font-medium text-gray-700">
                                            Cache durée (minutes)
                                            <span class="text-xs text-gray-500 block mt-1">Durée de cache des données</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[cache_duration_minutes]" name="settings[cache_duration_minutes]" value="{{ old('settings.cache_duration_minutes', $settings['application']->where('key', 'cache_duration_minutes')->first()->value ?? '10') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Session durée -->
                                    <div class="space-y-2">
                                        <label for="settings[session_lifetime_minutes]" class="block text-sm font-medium text-gray-700">
                                            Session durée (minutes)
                                            <span class="text-xs text-gray-500 block mt-1">Durée de vie des sessions</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[session_lifetime_minutes]" name="settings[session_lifetime_minutes]" value="{{ old('settings.session_lifetime_minutes', $settings['application']->where('key', 'session_lifetime_minutes')->first()->value ?? '120') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timezone -->
                                    <div class="space-y-2">
                                        <label for="settings[timezone]" class="block text-sm font-medium text-gray-700">
                                            Fuseau horaire
                                            <span class="text-xs text-gray-500 block mt-1">Fuseau horaire de l'application</span>
                                        </label>
                                        <select id="settings[timezone]" name="settings[timezone]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                            @php
                                                $timezones = ['Europe/Paris', 'UTC', 'America/New_York', 'Asia/Tokyo', 'Africa/Casablanca'];
                                                $currentTimezone = $settings['application']->where('key', 'timezone')->first()->value ?? 'Europe/Paris';
                                            @endphp
                                            @foreach($timezones as $tz)
                                                <option value="{{ $tz }}" {{ $currentTimezone == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Langue par défaut -->
                                    <div class="space-y-2">
                                        <label for="settings[default_language]" class="block text-sm font-medium text-gray-700">
                                            Langue par défaut
                                            <span class="text-xs text-gray-500 block mt-1">Langue par défaut de l'application</span>
                                        </label>
                                        <select id="settings[default_language]" name="settings[default_language]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                            @php
                                                $languages = ['fr' => 'Français', 'en' => 'English', 'es' => 'Español', 'ar' => 'العربية'];
                                                $currentLang = $settings['application']->where('key', 'default_language')->first()->value ?? 'fr';
                                            @endphp
                                            @foreach($languages as $code => $name)
                                                <option value="{{ $code }}" {{ $currentLang == $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sécurité Tab -->
                    <div id="safety-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Sécurité</h3>
                                        <p class="mt-1 text-sm text-gray-500">Paramètres de sécurité et de protection</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('safety')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Sécurité
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- SOS button -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Bouton SOS
                                            <span class="text-xs text-gray-500 block mt-1">Activer le bouton SOS dans l'application</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[sos_button_enabled]" value="false">
                                            <input type="checkbox" id="settings[sos_button_enabled]" name="settings[sos_button_enabled]" value="true" {{ ($settings['safety']->where('key', 'sos_button_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Partage trajet en direct -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Partage trajet en direct
                                            <span class="text-xs text-gray-500 block mt-1">Permettre le partage de position en direct</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[live_trip_sharing]" value="false">
                                            <input type="checkbox" id="settings[live_trip_sharing]" name="settings[live_trip_sharing]" value="true" {{ ($settings['safety']->where('key', 'live_trip_sharing')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Vérification selfie -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Vérification selfie
                                            <span class="text-xs text-gray-500 block mt-1">Vérification selfie pour les chauffeurs</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[selfie_verification]" value="false">
                                            <input type="checkbox" id="settings[selfie_verification]" name="settings[selfie_verification]" value="true" {{ ($settings['safety']->where('key', 'selfie_verification')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Enregistrement audio -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Enregistrement audio
                                            <span class="text-xs text-gray-500 block mt-1">Enregistrement audio pendant les trajets</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[audio_recording]" value="false">
                                            <input type="checkbox" id="settings[audio_recording]" name="settings[audio_recording]" value="true" {{ ($settings['safety']->where('key', 'audio_recording')->first()->value ?? 'false') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Masquer numéro -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Masquer numéro
                                            <span class="text-xs text-gray-500 block mt-1">Masquer les numéros de téléphone</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[mask_phone_numbers]" value="false">
                                            <input type="checkbox" id="settings[mask_phone_numbers]" name="settings[mask_phone_numbers]" value="true" {{ ($settings['safety']->where('key', 'mask_phone_numbers')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Vérification OTP -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Vérification OTP
                                            <span class="text-xs text-gray-500 block mt-1">Vérification OTP pour les trajets</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[otp_verification]" value="false">
                                            <input type="checkbox" id="settings[otp_verification]" name="settings[otp_verification]" value="true" {{ ($settings['safety']->where('key', 'otp_verification')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Vérification âge client -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Vérification âge client
                                            <span class="text-xs text-gray-500 block mt-1">Vérifier l'âge des clients</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[age_verification]" value="false">
                                            <input type="checkbox" id="settings[age_verification]" name="settings[age_verification]" value="true" {{ ($settings['safety']->where('key', 'age_verification')->first()->value ?? 'false') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Âge minimum client -->
                                    <div class="space-y-2">
                                        <label for="settings[minimum_rider_age]" class="block text-sm font-medium text-gray-700">
                                            Âge minimum client
                                            <span class="text-xs text-gray-500 block mt-1">Âge minimum requis pour utiliser l'application</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[minimum_rider_age]" name="settings[minimum_rider_age]" value="{{ old('settings.minimum_rider_age', $settings['safety']->where('key', 'minimum_rider_age')->first()->value ?? '18') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <!-- Temps SOS réponse -->
                                    <div class="space-y-2">
                                        <label for="settings[sos_response_time_seconds]" class="block text-sm font-medium text-gray-700">
                                            Temps SOS réponse (sec)
                                            <span class="text-xs text-gray-500 block mt-1">Temps maximum pour répondre au SOS</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[sos_response_time_seconds]" name="settings[sos_response_time_seconds]" value="{{ old('settings.sos_response_time_seconds', $settings['safety']->where('key', 'sos_response_time_seconds')->first()->value ?? '60') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">sec</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Géofencing Tab -->
                    <div id="geofencing-tab" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Géofencing</h3>
                                        <p class="mt-1 text-sm text-gray-500">Paramètres de zone et de géolocalisation</p>
                                    </div>
                                    <button type="button" onclick="saveCategory('geofencing')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Sauvegarder Géofencing
                                    </button>
                                </div>
                            </div>
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Activer géofencing -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Activer géofencing
                                            <span class="text-xs text-gray-500 block mt-1">Activer le géofencing</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[geofencing_enabled]" value="false">
                                            <input type="checkbox" id="settings[geofencing_enabled]" name="settings[geofencing_enabled]" value="true" {{ ($settings['geofencing']->where('key', 'geofencing_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Zone de service -->
                                    <div class="space-y-2">
                                        <label for="settings[service_area_radius_km]" class="block text-sm font-medium text-gray-700">
                                            Zone de service (km)
                                            <span class="text-xs text-gray-500 block mt-1">Rayon de la zone de service</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[service_area_radius_km]" name="settings[service_area_radius_km]" value="{{ old('settings.service_area_radius_km', $settings['geofencing']->where('key', 'service_area_radius_km')->first()->value ?? '50') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Zone hors service -->
                                    <div class="space-y-2">
                                        <label for="settings[out_of_service_area_penalty]" class="block text-sm font-medium text-gray-700">
                                            Pénalité hors zone
                                            <span class="text-xs text-gray-500 block mt-1">Pénalité pour trajet hors zone</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.01" id="settings[out_of_service_area_penalty]" name="settings[out_of_service_area_penalty]" value="{{ old('settings.out_of_service_area_penalty', $settings['geofencing']->where('key', 'out_of_service_area_penalty')->first()->value ?? '10.00') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">DT</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Précision GPS -->
                                    <div class="space-y-2">
                                        <label for="settings[gps_accuracy_meters]" class="block text-sm font-medium text-gray-700">
                                            Précision GPS (mètres)
                                            <span class="text-xs text-gray-500 block mt-1">Précision requise pour la géolocalisation</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[gps_accuracy_meters]" name="settings[gps_accuracy_meters]" value="{{ old('settings.gps_accuracy_meters', $settings['geofencing']->where('key', 'gps_accuracy_meters')->first()->value ?? '50') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">m</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Intervalle mise à jour position -->
                                    <div class="space-y-2">
                                        <label for="settings[location_update_interval_seconds]" class="block text-sm font-medium text-gray-700">
                                            Intervalle position (sec)
                                            <span class="text-xs text-gray-500 block mt-1">Intervalle de mise à jour de la position</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="1" id="settings[location_update_interval_seconds]" name="settings[location_update_interval_seconds]" value="{{ old('settings.location_update_interval_seconds', $settings['geofencing']->where('key', 'location_update_interval_seconds')->first()->value ?? '10') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">sec</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Zone interdite -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Zones interdites
                                            <span class="text-xs text-gray-500 block mt-1">Activer les zones interdites</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[restricted_zones_enabled]" value="false">
                                            <input type="checkbox" id="settings[restricted_zones_enabled]" name="settings[restricted_zones_enabled]" value="true" {{ ($settings['geofencing']->where('key', 'restricted_zones_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Zone aéroport -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Zone aéroport
                                            <span class="text-xs text-gray-500 block mt-1">Zone spéciale pour l'aéroport</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[airport_zone_enabled]" value="false">
                                            <input type="checkbox" id="settings[airport_zone_enabled]" name="settings[airport_zone_enabled]" value="true" {{ ($settings['geofencing']->where('key', 'airport_zone_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Zone centre-ville -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Zone centre-ville
                                            <span class="text-xs text-gray-500 block mt-1">Zone spéciale pour le centre-ville</span>
                                        </label>
                                        <div class="flex items-center">
                                            <input type="hidden" name="settings[downtown_zone_enabled]" value="false">
                                            <input type="checkbox" id="settings[downtown_zone_enabled]" name="settings[downtown_zone_enabled]" value="true" {{ ($settings['geofencing']->where('key', 'downtown_zone_enabled')->first()->value ?? 'true') === 'true' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900">Activé</span>
                                        </div>
                                    </div>

                                    <!-- Rayon zone aéroport -->
                                    <div class="space-y-2">
                                        <label for="settings[airport_zone_radius_km]" class="block text-sm font-medium text-gray-700">
                                            Rayon zone aéroport (km)
                                            <span class="text-xs text-gray-500 block mt-1">Rayon de la zone aéroport</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[airport_zone_radius_km]" name="settings[airport_zone_radius_km]" value="{{ old('settings.airport_zone_radius_km', $settings['geofencing']->where('key', 'airport_zone_radius_km')->first()->value ?? '2.0') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rayon zone centre-ville -->
                                    <div class="space-y-2">
                                        <label for="settings[downtown_zone_radius_km]" class="block text-sm font-medium text-gray-700">
                                            Rayon zone centre-ville (km)
                                            <span class="text-xs text-gray-500 block mt-1">Rayon de la zone centre-ville</span>
                                        </label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input type="number" step="0.1" id="settings[downtown_zone_radius_km]" name="settings[downtown_zone_radius_km]" value="{{ old('settings.downtown_zone_radius_km', $settings['geofencing']->where('key', 'downtown_zone_radius_km')->first()->value ?? '1.5') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pl-3 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">km</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Show selected tab
            document.getElementById(tabId + '-tab').classList.remove('hidden');

            // Update active tab style
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('border-blue-500', 'text-blue-600');
                link.classList.add('border-transparent', 'text-gray-500');
            });

            const activeLink = document.querySelector(`a[href="#${tabId}"]`);
            activeLink.classList.remove('border-transparent', 'text-gray-500');
            activeLink.classList.add('border-blue-500', 'text-blue-600');

            // Store active tab in localStorage
            localStorage.setItem('activeSettingsTab', tabId);
        }

        function saveCategory(category) {
            // Create a form for the specific category
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/settings/category/${category}`;
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfToken);

            // Add method spoofing
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            form.appendChild(method);

            // Get all inputs for this category
            const categoryTab = document.getElementById(`${category}-tab`);
            const inputs = categoryTab.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    const clone = input.cloneNode(true);
                    if (!clone.checked) {
                        // For checkboxes, we need to ensure false value is sent
                        const falseInput = document.createElement('input');
                        falseInput.type = 'hidden';
                        falseInput.name = input.name;
                        falseInput.value = 'false';
                        form.appendChild(falseInput);
                    } else {
                        form.appendChild(clone);
                    }
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        const clone = input.cloneNode(true);
                        form.appendChild(clone);
                    }
                } else if (input.tagName === 'SELECT') {
                    const clone = input.cloneNode(true);
                    form.appendChild(clone);
                } else if (input.type === 'text' || input.type === 'number' || input.type === 'email' || input.type === 'hidden') {
                    const clone = input.cloneNode(true);
                    form.appendChild(clone);
                }
            });

            // Submit the form
            document.body.appendChild(form);
            form.submit();
        }

        function saveAllSettings() {
            document.getElementById('settingsForm').submit();
        }

        function resetToDefaults() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ? Cette action est irréversible.')) {
                // You would typically make an AJAX call to reset settings
                // For now, we'll reload the page
                window.location.href = '/settings/reset';
            }
        }

        // Initialize first tab as active or restore from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedTab = localStorage.getItem('activeSettingsTab');
            if (savedTab && document.getElementById(savedTab + '-tab')) {
                showTab(savedTab);
            } else {
                const firstTab = document.querySelector('.tab-link');
                if (firstTab) {
                    const firstTabId = firstTab.getAttribute('href').substring(1);
                    showTab(firstTabId);
                }
            }
        });
    </script>
    @endpush
</x-app-layout>