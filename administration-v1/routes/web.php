<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\DriversController;
use App\Http\Controllers\Users\ClientsController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Trips\TripController;
use App\Http\Livewire\DriversTable;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'dashboard')->middleware(['auth'])->name('dashboard');

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Map Live Route
Route::view('map-live', 'map-live')
    ->middleware(['auth'])
    ->name('map-live');

// Chauffeurs Routes
Route::get('/chauffeurs', [DriversController::class, 'index'])->name('chauffeurs');

// Clients Routes
Route::get('/clients', [ClientsController::class, 'index'])->middleware(['auth'])->name('clients');



// settings Routes
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
Route::put('/settings/category/{category}', [SettingsController::class, 'updateCategory'])->name('settings.category.update');
Route::post('/settings/reset', [SettingsController::class, 'resetToDefaults'])->name('settings.reset');


//trips Routes
Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
Route::get('/trips/{trip}/single-map', [TripController::class, 'singleMap'])->name('trips.single-map');
Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
Route::get('/trips/export', [TripController::class, 'export'])->name('trips.export');
Route::get('/trips/analytics', [TripController::class, 'analytics'])->name('trips.analytics');





// Courses Routes
Route::view('courses', 'courses.index')
    ->middleware(['auth'])
    ->name('courses');

Route::view('courses/create', 'courses.create')
    ->middleware(['auth'])
    ->name('courses.create');

Route::view('courses/{id}', 'courses.show')
    ->middleware(['auth'])
    ->name('courses.show');

Route::view('courses/{id}/edit', 'courses.edit')
    ->middleware(['auth'])
    ->name('courses.edit');

// Catégories Routes
Route::view('categories', 'categories.index')
    ->middleware(['auth'])
    ->name('categories');

Route::view('categories/create', 'categories.create')
    ->middleware(['auth'])
    ->name('categories.create');

Route::view('categories/{id}', 'categories.show')
    ->middleware(['auth'])
    ->name('categories.show');

Route::view('categories/{id}/edit', 'categories.edit')
    ->middleware(['auth'])
    ->name('categories.edit');

// Devis Routes
Route::view('devis', 'devis.index')
    ->middleware(['auth'])
    ->name('devis');

Route::view('devis/create', 'devis.create')
    ->middleware(['auth'])
    ->name('devis.create');

Route::view('devis/{id}', 'devis.show')
    ->middleware(['auth'])
    ->name('devis.show');

Route::view('devis/{id}/edit', 'devis.edit')
    ->middleware(['auth'])
    ->name('devis.edit');

// Paiements Routes
Route::view('paiements', 'paiements.index')
    ->middleware(['auth'])
    ->name('paiements');

Route::view('paiements/create', 'paiements.create')
    ->middleware(['auth'])
    ->name('paiements.create');

Route::view('paiements/{id}', 'paiements.show')
    ->middleware(['auth'])
    ->name('paiements.show');

Route::view('paiements/{id}/edit', 'paiements.edit')
    ->middleware(['auth'])
    ->name('paiements.edit');

// Rapports Routes
Route::view('rapports', 'rapports.index')
    ->middleware(['auth'])
    ->name('rapports');

Route::view('rapports/chauffeurs', 'rapports.chauffeurs')
    ->middleware(['auth'])
    ->name('rapports.chauffeurs');

Route::view('rapports/clients', 'rapports.clients')
    ->middleware(['auth'])
    ->name('rapports.clients');

Route::view('rapports/courses', 'rapports.courses')
    ->middleware(['auth'])
    ->name('rapports.courses');

Route::view('rapports/financiers', 'rapports.financiers')
    ->middleware(['auth'])
    ->name('rapports.financiers');

require __DIR__.'/auth.php';