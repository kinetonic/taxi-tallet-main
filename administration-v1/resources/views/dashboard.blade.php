<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
            <div class="relative">
                <input type="date" placeholder="Rechercher..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div>
                

                <!-- Contenu principal -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Section Vue d'ensemble -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Vue d'ensemble</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600">5</p>
                                <p class="text-sm text-gray-600">Disponibles</p>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <p class="text-2xl font-bold text-red-600">6</p>
                                <p class="text-sm text-gray-600">Non disponibles</p>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <p class="text-2xl font-bold text-yellow-600">0</p>
                                <p class="text-sm text-gray-600">En route pour prise en charge</p>
                            </div>
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600">0</p>
                                <p class="text-sm text-gray-600">Arrivé / Sur lieu de prise en charge</p>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <p class="text-2xl font-bold text-purple-600">0</p>
                                <p class="text-sm text-gray-600">En route pour dépose</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section Statistiques utilisateur -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Utilisateur 37 -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Utilisateur 37</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                                    <span class="text-gray-700">Chauffeurs actifs</span>
                                    <span class="text-xl font-bold text-green-600">24</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-red-50 rounded">
                                    <span class="text-gray-700">Chauffeurs inactifs</span>
                                    <span class="text-xl font-bold text-red-600">0</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                                    <span class="text-gray-700">Chauffeurs</span>
                                    <span class="text-xl font-bold text-blue-600">24</span>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques des trajets -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques des trajets</h3>
                            <div class="space-y-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Total des trajets</p>
                                    <p class="text-2xl font-bold text-gray-900">1 247</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-blue-50 rounded">
                                        <p class="text-sm text-gray-600">Aujourd'hui</p>
                                        <p class="text-xl font-bold text-blue-600">24</p>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded">
                                        <p class="text-sm text-gray-600">Total</p>
                                        <p class="text-xl font-bold text-green-600">1 247</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trajets annulés -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statut des trajets</h3>
                            <div class="space-y-4">
                                <div class="text-center p-4 bg-red-50 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Trajets annulés</p>
                                    <p class="text-2xl font-bold text-red-600">37</p>
                                </div>
                                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Trajets annulés</p>
                                    <p class="text-2xl font-bold text-yellow-600">37</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Carte -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Carte de la zone de service - Tunisie, Ariana</h3>
                        <div class="h-96 rounded-lg border border-gray-200 relative" id="mapContainer">
                            <div id="map"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-item {
            transition: all 0.2s ease-in-out;
        }
        
        .nav-item:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }
        
        .stat-card {
            transition: all 0.2s ease-in-out;
        }
        
        .stat-card:hover {
            transform: translateY(-1px);
        }
    </style>
</x-app-layout>