<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DriversTable extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $statusFilter = '';
    public $sortField = 'first_name';
    public $sortDirection = 'asc';
    public $selectedDrivers = []; // Initialize as empty array
    public $selectAll = false;

    // Form fields
    public $showModal = false;
    public $editingDriver = null;
    public $form = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'password' => '',
        'driver_license_number' => '',
        'vehicle_type' => '',
        'vehicle_model' => '',
        'vehicle_year' => '',
        'vehicle_plate_number' => '',
        'vehicle_color' => '',
        'driver_status' => 'available',
        'is_online' => false,
        'is_active' => true,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'first_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $rules = [
        'form.first_name' => 'required|string|max:255',
        'form.last_name' => 'required|string|max:255',
        'form.email' => 'required|email|unique:users,email',
        'form.phone' => 'required|string|max:20',
        'form.password' => 'sometimes|min:8',
        'form.driver_license_number' => 'required|string',
        'form.vehicle_type' => 'required|string',
        'form.vehicle_model' => 'required|string',
        'form.vehicle_year' => 'nullable|integer',
        'form.vehicle_plate_number' => 'required|string',
        'form.vehicle_color' => 'nullable|string',
        'form.driver_status' => 'required|in:available,on_trip,offline,maintenance',
        'form.is_online' => 'boolean',
        'form.is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->selectedDrivers = is_array($this->selectedDrivers) ? $this->selectedDrivers : [];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedDrivers = $this->drivers->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedDrivers = [];
        }
    }

    public function updatedSelectedDrivers()
    {
        $this->selectAll = false;
    }

    public function getDriversProperty()
    {
        return User::where('type', 'driver')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('phone', 'like', '%' . $this->search . '%')
                          ->orWhere('vehicle_plate_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'online') {
                    $query->where('is_online', true);
                } elseif ($this->statusFilter === 'offline_status') {
                    $query->where('is_online', false);
                } else {
                    $query->where('driver_status', $this->statusFilter);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function showCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editingDriver = null;
    }

    public function showEditModal($driverId)
    {
        $this->editingDriver = User::find($driverId);
        
        if ($this->editingDriver) {
            $this->form = [
                'first_name' => $this->editingDriver->first_name,
                'last_name' => $this->editingDriver->last_name,
                'email' => $this->editingDriver->email,
                'phone' => $this->editingDriver->phone,
                'password' => '',
                'driver_license_number' => $this->editingDriver->driver_license_number,
                'vehicle_type' => $this->editingDriver->vehicle_type,
                'vehicle_model' => $this->editingDriver->vehicle_model,
                'vehicle_year' => $this->editingDriver->vehicle_year,
                'vehicle_plate_number' => $this->editingDriver->vehicle_plate_number,
                'vehicle_color' => $this->editingDriver->vehicle_color,
                'driver_status' => $this->editingDriver->driver_status,
                'is_online' => $this->editingDriver->is_online,
                'is_active' => $this->editingDriver->is_active,
            ];
            
            $this->showModal = true;
        }
    }

    public function resetForm()
    {
        $this->form = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'password' => '',
            'driver_license_number' => '',
            'vehicle_type' => '',
            'vehicle_model' => '',
            'vehicle_year' => '',
            'vehicle_plate_number' => '',
            'vehicle_color' => '',
            'driver_status' => 'available',
            'is_online' => false,
            'is_active' => true,
        ];
        $this->resetErrorBag();
    }

    public function saveDriver()
    {
        if ($this->editingDriver) {
            $this->rules['form.email'] = 'required|email|unique:users,email,' . $this->editingDriver->id;
            $this->rules['form.password'] = 'nullable|min:8';
        }

        $this->validate();

        $driverData = [
            'type' => 'driver',
            'first_name' => $this->form['first_name'],
            'last_name' => $this->form['last_name'],
            'email' => $this->form['email'],
            'phone' => $this->form['phone'],
            'driver_license_number' => $this->form['driver_license_number'],
            'vehicle_type' => $this->form['vehicle_type'],
            'vehicle_model' => $this->form['vehicle_model'],
            'vehicle_year' => $this->form['vehicle_year'],
            'vehicle_plate_number' => $this->form['vehicle_plate_number'],
            'vehicle_color' => $this->form['vehicle_color'],
            'driver_status' => $this->form['driver_status'],
            'is_online' => $this->form['is_online'],
            'is_active' => $this->form['is_active'],
        ];

        if (!empty($this->form['password'])) {
            $driverData['password'] = Hash::make($this->form['password']);
        }

        if ($this->editingDriver) {
            $this->editingDriver->update($driverData);
            $message = 'Chauffeur mis à jour avec succès.';
        } else {
            User::create($driverData);
            $message = 'Chauffeur créé avec succès.';
        }

        $this->showModal = false;
        $this->resetForm();
        
        session()->flash('message', $message);
    }

    public function updateStatus($driverId, $status)
    {
        $driver = User::where('id', $driverId)->where('type', 'driver')->first();
        if ($driver) {
            $driver->update(['driver_status' => $status]);
            session()->flash('message', 'Statut du chauffeur mis à jour avec succès.');
        }
    }

    public function toggleOnlineStatus($driverId)
    {
        $driver = User::where('id', $driverId)->where('type', 'driver')->first();
        if ($driver) {
            $driver->update([
                'is_online' => !$driver->is_online,
                'last_online_at' => now()
            ]);
            session()->flash('message', 'Statut en ligne mis à jour avec succès.');
        }
    }

    public function toggleActiveStatus($driverId)
    {
        $driver = User::where('id', $driverId)->where('type', 'driver')->first();
        if ($driver) {
            $driver->update(['is_active' => !$driver->is_active]);
            session()->flash('message', 'Statut actif mis à jour avec succès.');
        }
    }

    public function deleteSelected()
    {
        User::where('type', 'driver')
            ->whereIn('id', $this->selectedDrivers)
            ->delete();
            
        $this->selectedDrivers = [];
        $this->selectAll = false;
        session()->flash('message', 'Chauffeurs supprimés avec succès.');
    }

    public function deleteDriver($driverId)
    {
        $driver = User::where('id', $driverId)->where('type', 'driver')->first();
        if ($driver) {
            $driver->delete();
            session()->flash('message', 'Chauffeur supprimé avec succès.');
        }
    }

    public function render()
    {
        return view('livewire.drivers-table', [
            'drivers' => $this->drivers,
        ]);
    }
}