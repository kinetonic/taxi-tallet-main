import './bootstrap';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Initialize Leaflet only when needed
window.initMap = function(elementId, options = {}) {
    const defaultOptions = {
        center: [36.860117, 10.193371],
        zoom: 13,
        ...options
    };
    
    const map = L.map(elementId).setView(defaultOptions.center, defaultOptions.zoom);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);
    
    return map;
};

// Custom taxi icons
window.createTaxiIcon = function(status) {
    const color = status === 'available' ? '#10B981' : '#EF4444';
    const borderColor = status === 'available' ? '#059669' : '#DC2626';
    
    return L.divIcon({
        html: `
            <div class="relative">
                <div class="w-10 h-10 rounded-full bg-white shadow-lg flex items-center justify-center border-2" 
                     style="border-color: ${borderColor}">
                    <svg class="w-6 h-6" style="color: ${color}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h.05a2.5 2.5 0 014.9 0H17a1 1 0 001-1v-5h-1.41a.5.5 0 01-.47-.33L14.64 4H3zm11.24 0a.5.5 0 01.47.33L16.64 8H15a1 1 0 00-1 1v3.05a2.5 2.5 0 00-2 0V9a1 1 0 00-1-1H7.41a.5.5 0 01-.47-.33L5.76 4H14.24z"></path>
                    </svg>
                </div>
                <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white" 
                     style="background-color: ${color}"></div>
            </div>
        `,
        className: 'custom-taxi-icon',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });
};

// Initialize settings tab functionality
window.showTab = function(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });

    // Show selected tab
    const selectedTab = document.getElementById(tabId + '-tab');
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
    }

    // Update active tab style
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('border-blue-500', 'text-blue-600');
        link.classList.add('border-transparent', 'text-gray-500');
    });

    const activeLink = document.querySelector(`a[href="#${tabId}"]`);
    if (activeLink) {
        activeLink.classList.remove('border-transparent', 'text-gray-500');
        activeLink.classList.add('border-blue-500', 'text-blue-600');
    }

    // Store active tab in localStorage
    localStorage.setItem('activeSettingsTab', tabId);
};

window.saveCategory = function(category) {
    if (confirm('Sauvegarder les paramètres de cette catégorie ?')) {
        const form = document.getElementById('settingsForm');
        if (form) {
            // Create hidden input to specify which category to save
            const categoryInput = document.createElement('input');
            categoryInput.type = 'hidden';
            categoryInput.name = 'category';
            categoryInput.value = category;
            form.appendChild(categoryInput);
            
            // Submit form
            form.submit();
        }
    }
};

window.saveAllSettings = function() {
    if (confirm('Sauvegarder tous les paramètres ?')) {
        document.getElementById('settingsForm').submit();
    }
};

window.resetToDefaults = function() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ? Cette action est irréversible.')) {
        window.location.href = '/admin/settings/reset';
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tab functionality
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

    // Add event listeners for tabs
    document.querySelectorAll('.tab-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.startsWith('#')) {
            const tabId = href.substring(1);
            link.addEventListener('click', function(e) {
                e.preventDefault();
                showTab(tabId);
            });
        }
    });

    // Add event listeners to buttons
    document.querySelectorAll('.save-all-btn').forEach(btn => {
        btn.addEventListener('click', saveAllSettings);
    });

    document.querySelectorAll('.reset-btn').forEach(btn => {
        btn.addEventListener('click', resetToDefaults);
    });

    document.querySelectorAll('[onclick^="saveCategory"]').forEach(btn => {
        const onclick = btn.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/saveCategory\('([^']+)'\)/);
            if (match) {
                const category = match[1];
                btn.removeAttribute('onclick');
                btn.addEventListener('click', () => saveCategory(category));
            }
        }
    });

    // Initialize map if element exists
    const mapElement = document.getElementById('map');
    if (mapElement) {
        try {
            const map = initMap('map');
            
            // Sample taxi data for Ariana, Tunisia
            const taxiData = [
                { lat: 36.8625, lng: 10.1956, status: 'available', driver: 'Taxi 101' },
                { lat: 36.8589, lng: 10.1902, status: 'available', driver: 'Taxi 102' },
                { lat: 36.8632, lng: 10.1987, status: 'busy', driver: 'Taxi 103' },
                { lat: 36.8571, lng: 10.1923, status: 'available', driver: 'Taxi 104' },
                { lat: 36.8618, lng: 10.1865, status: 'busy', driver: 'Taxi 105' },
                { lat: 36.8594, lng: 10.2001, status: 'available', driver: 'Taxi 106' },
                { lat: 36.8650, lng: 10.1889, status: 'busy', driver: 'Taxi 107' },
                { lat: 36.8567, lng: 10.1964, status: 'available', driver: 'Taxi 108' },
                { lat: 36.8641, lng: 10.1915, status: 'available', driver: 'Taxi 109' },
                { lat: 36.8579, lng: 10.1893, status: 'busy', driver: 'Taxi 110' },
                { lat: 36.8605, lng: 10.1978, status: 'available', driver: 'Taxi 111' }
            ];

            // Add taxi markers
            taxiData.forEach(taxi => {
                const icon = createTaxiIcon(taxi.status);
                const marker = L.marker([taxi.lat, taxi.lng], { icon }).addTo(map);
                
                marker.bindPopup(`
                    <div class="p-2">
                        <div class="font-semibold text-lg">${taxi.driver}</div>
                        <div class="flex items-center mt-1 ${taxi.status === 'available' ? 'text-green-600' : 'text-red-600'}">
                            <div class="w-2 h-2 rounded-full ${taxi.status === 'available' ? 'bg-green-500' : 'bg-red-500'} mr-2"></div>
                            ${taxi.status === 'available' ? 'Disponible' : 'En course'}
                        </div>
                        <div class="text-xs text-gray-500 mt-2">Cliquez pour réserver</div>
                    </div>
                `);
            });

            // Add center marker
            const centerIcon = L.divIcon({
                html: `
                    <div class="relative">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                `,
                className: 'custom-center-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            L.marker([36.860117, 10.193371], { icon: centerIcon })
                .addTo(map)
                .bindPopup('<div class="font-semibold">Centre d\'Ariana</div><div class="text-sm text-gray-600">Zone principale de service</div>')
                .openPopup();

            // Add CSS for markers
            const style = document.createElement('style');
            style.textContent = `
                .custom-taxi-icon {
                    transition: transform 0.2s;
                }
                .custom-taxi-icon:hover {
                    transform: scale(1.1);
                }
                .custom-center-icon {
                    filter: drop-shadow(0 2px 4px rgba(59, 130, 246, 0.5));
                }
            `;
            document.head.appendChild(style);

        } catch (error) {
            console.error('Error initializing map:', error);
        }
    }
});