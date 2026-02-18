<div>
    <!-- Search and Filters -->
    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 w-full md:w-auto">
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.debounce.300ms="search"
                    placeholder="Rechercher un chauffeur..." 
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-80"
                >
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            
            <select wire:model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-48">
                <option value="">Tous les statuts</option>
                <option value="available">Disponible</option>
                <option value="on_trip">En course</option>
                <option value="offline">Hors ligne</option>
                <option value="maintenance">En maintenance</option>
                <option value="online">En ligne</option>
                <option value="offline_status">Hors ligne</option>
            </select>
        </div>
        
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <select wire:model="perPage" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="5">5 par page</option>
                <option value="10">10 par page</option>
                <option value="25">25 par page</option>
                <option value="50">50 par page</option>
            </select>
            
            @if(is_array($selectedDrivers) && count($selectedDrivers) > 0)
                <button 
                    wire:click="deleteSelected" 
                    wire:confirm="Êtes-vous sûr de vouloir supprimer ces chauffeurs?"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center"
                >
                    <i class="fas fa-trash mr-2"></i>
                    Supprimer ({{ count($selectedDrivers) }})
                </button>
            @endif
            
            <button 
                wire:click="showCreateModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
            >
                <i class="fas fa-plus mr-2"></i>
                Nouveau Chauffeur
            </button>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input 
                            type="checkbox" 
                            wire:model="selectAll"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('first_name')">
                        <div class="flex items-center">
                            Chauffeur
                            @if($sortField === 'first_name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('vehicle_plate_number')">
                        <div class="flex items-center">
                            Véhicule
                            @if($sortField === 'vehicle_plate_number')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('driver_status')">
                        <div class="flex items-center">
                            Statut
                            @if($sortField === 'driver_status')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_trips')">
                        <div class="flex items-center">
                            Courses
                            @if($sortField === 'total_trips')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('rating')">
                        <div class="flex items-center">
                            Note
                            @if($sortField === 'rating')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('earnings')">
                        <div class="flex items-center">
                            Gains
                            @if($sortField === 'earnings')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($drivers as $driver)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input 
                                type="checkbox" 
                                value="{{ $driver->id }}"
                                wire:model="selectedDrivers"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ $driver->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($driver->full_name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ $driver->full_name }}">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $driver->full_name }}</div>
                                    <div class="text-sm text-gray-500">Permis: {{ $driver->driver_license_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $driver->phone ?? 'Non renseigné' }}</div>
                            <div class="text-sm text-gray-500">{{ $driver->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $driver->vehicle_type ?? 'Non spécifié' }} 
                                {{ $driver->vehicle_model ?? '' }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $driver->vehicle_plate_number ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-400">{{ $driver->vehicle_year ?? '' }} • {{ $driver->vehicle_color ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-green-100 text-green-800',
                                        'on_trip' => 'bg-blue-100 text-blue-800',
                                        'offline' => 'bg-gray-100 text-gray-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                    $statusLabels = [
                                        'available' => 'Disponible',
                                        'on_trip' => 'En course',
                                        'offline' => 'Hors ligne',
                                        'maintenance' => 'Maintenance'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$driver->driver_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$driver->driver_status] ?? $driver->driver_status }}
                                </span>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 rounded-full {{ $driver->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                    <span class="text-xs text-gray-500">{{ $driver->is_online ? 'En ligne' : 'Hors ligne' }}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 rounded-full {{ $driver->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-xs text-gray-500">{{ $driver->is_active ? 'Actif' : 'Inactif' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="text-center">
                                <div class="font-semibold">{{ $driver->total_trips ?? 0 }}</div>
                                <div class="text-xs text-gray-500">courses</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-star text-yellow-400 mr-1 text-sm"></i>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($driver->rating ?? 0, 1) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="text-center">
                                <div class="font-semibold text-green-600">{{ number_format($driver->earnings ?? 0, 2) }} €</div>
                                <div class="text-xs text-gray-500">total</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- Online Status Toggle -->
                                <button 
                                    wire:click="toggleOnlineStatus({{ $driver->id }})"
                                    class="p-1 rounded {{ $driver->is_online ? 'bg-green-100 text-green-600 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                    title="{{ $driver->is_online ? 'Mettre hors ligne' : 'Mettre en ligne' }}"
                                >
                                    <i class="fas fa-power-off text-xs"></i>
                                </button>

                                <!-- Active Status Toggle -->
                                <button 
                                    wire:click="toggleActiveStatus({{ $driver->id }})"
                                    class="p-1 rounded {{ $driver->is_active ? 'bg-green-100 text-green-600 hover:bg-green-200' : 'bg-red-100 text-red-600 hover:bg-red-200' }}"
                                    title="{{ $driver->is_active ? 'Désactiver' : 'Activer' }}"
                                >
                                    <i class="fas fa-user-{{ $driver->is_active ? 'check' : 'slash' }} text-xs"></i>
                                </button>

                                <!-- Status Quick Actions -->
                                <select 
                                    wire:change="updateStatus({{ $driver->id }}, $event.target.value)"
                                    class="text-xs border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="available" {{ $driver->driver_status == 'available' ? 'selected' : '' }}>Disponible</option>
                                    <option value="on_trip" {{ $driver->driver_status == 'on_trip' ? 'selected' : '' }}>En course</option>
                                    <option value="offline" {{ $driver->driver_status == 'offline' ? 'selected' : '' }}>Hors ligne</option>
                                    <option value="maintenance" {{ $driver->driver_status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                
                                <button 
                                    wire:click="showEditModal({{ $driver->id }})"
                                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                                    title="Modifier"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button 
                                    wire:click="deleteDriver({{ $driver->id }})"
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer ce chauffeur?"
                                    class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                                    title="Supprimer"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                            <i class="fas fa-users-slash text-2xl text-gray-300 mb-2"></i>
                            <div>Aucun chauffeur trouvé.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $drivers->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ $editingDriver ? 'Modifier le chauffeur' : 'Nouveau chauffeur' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveDriver" class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900">Informations personnelles</h4>
                                
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom *</label>
                                    <input type="text" wire:model="form.first_name" id="first_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Nom *</label>
                                    <input type="text" wire:model="form.last_name" id="last_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                    <input type="email" wire:model="form.email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone *</label>
                                    <input type="text" wire:model="form.phone" id="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">
                                        Mot de passe {{ $editingDriver ? '(laisser vide pour ne pas changer)' : '*' }}
                                    </label>
                                    <input type="password" wire:model="form.password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Vehicle Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900">Informations du véhicule</h4>
                                
                                <div>
                                    <label for="driver_license_number" class="block text-sm font-medium text-gray-700">Numéro de permis *</label>
                                    <input type="text" wire:model="form.driver_license_number" id="driver_license_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.driver_license_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="vehicle_type" class="block text-sm font-medium text-gray-700">Type de véhicule *</label>
                                    <input type="text" wire:model="form.vehicle_type" id="vehicle_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.vehicle_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="vehicle_model" class="block text-sm font-medium text-gray-700">Modèle *</label>
                                    <input type="text" wire:model="form.vehicle_model" id="vehicle_model" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.vehicle_model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="vehicle_plate_number" class="block text-sm font-medium text-gray-700">Plaque d'immatriculation *</label>
                                    <input type="text" wire:model="form.vehicle_plate_number" id="vehicle_plate_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.vehicle_plate_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="vehicle_year" class="block text-sm font-medium text-gray-700">Année</label>
                                        <input type="number" wire:model="form.vehicle_year" id="vehicle_year" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="vehicle_color" class="block text-sm font-medium text-gray-700">Couleur</label>
                                        <input type="text" wire:model="form.vehicle_color" id="vehicle_color" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t">
                            <div>
                                <label for="driver_status" class="block text-sm font-medium text-gray-700">Statut *</label>
                                <select wire:model="form.driver_status" id="driver_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="available">Disponible</option>
                                    <option value="on_trip">En course</option>
                                    <option value="offline">Hors ligne</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                                @error('form.driver_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="form.is_online" id="is_online" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="is_online" class="ml-2 block text-sm text-gray-700">En ligne</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="form.is_active" id="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">Actif</label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ $editingDriver ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('message') }}
        </div>
    @endif
</div>