<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Carte en Direct</h1>
            <div class="flex gap-3">
                <div class="relative">
                    <button id="refreshBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200 shadow-sm">
                        <i class="fas fa-sync-alt"></i>
                        <span>Actualiser</span>
                    </button>
                </div>
                <div class="relative">
                    <input type="date" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    <i class="fas fa-calendar absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-100 p-3 mr-4">
                            <i class="fas fa-car text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Disponibles</p>
                            <p class="text-xl font-bold text-gray-800">12</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-red-100 p-3 mr-4">
                            <i class="fas fa-user-clock text-red-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">En Trajet</p>
                            <p class="text-xl font-bold text-gray-800">8</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-100 p-3 mr-4">
                            <i class="fas fa-road text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Trajets Aujourd'hui</p>
                            <p class="text-xl font-bold text-gray-800">24</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="rounded-full bg-yellow-100 p-3 mr-4">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Annulés</p>
                            <p class="text-xl font-bold text-gray-800">3</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center flex-wrap gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Zone de Service - Tunisie, Ariana</h3>
                            <p class="text-sm text-gray-500 mt-1">Surveillance en temps réel des chauffeurs</p>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex items-center gap-2 px-3 py-1 bg-green-50 rounded-full">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-700">Disponible</span>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1 bg-red-50 rounded-full">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="text-sm text-gray-700">Occupé</span>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1 bg-yellow-50 rounded-full">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <span class="text-sm text-gray-700">En Route</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="map-container rounded-lg border border-gray-200 relative bg-gray-100" id="mapContainer">
                        <div id="map" class="h-96 w-full rounded-lg"></div>
                        <!-- Map Controls -->
                        <div class="absolute top-4 right-4 bg-white rounded-lg shadow-lg p-3 z-10">
                            <div class="flex gap-2">
                                <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-plus text-gray-600"></i>
                                </button>
                                <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-minus text-gray-600"></i>
                                </button>
                                <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-crosshairs text-gray-600"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Drivers List -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Chauffeurs Actifs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chauffeur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localisation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière Mise à Jour</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold">MJ</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Mohamed Jlassi</div>
                                            <div class="text-sm text-gray-500">Toyota Corolla</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Disponible
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ariana Ville</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Il y a 2 min</td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <span class="text-green-600 font-semibold">AL</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Amira Laouini</div>
                                            <div class="text-sm text-gray-500">Renault Clio</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        En Route
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Route La Goulette</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Il y a 5 min</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        #refreshBtn:hover i {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }
        
        .map-container {
            min-height: 400px;
        }
        
        @media (max-width: 768px) {
            .map-container {
                min-height: 300px;
            }
        }
    </style>

    <script>
        document.getElementById('refreshBtn').addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 300);
            
            // Simuler l'actualisation des données
            console.log('Actualisation des données...');
        });
    </script>
</x-app-layout>