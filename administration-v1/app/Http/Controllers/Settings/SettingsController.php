<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::getGroupedSettings();
        
        // Define all categories with their display names
        $categories = [
            'pricing' => 'Tarification',
            'commission' => 'Commissions',
            'vehicles' => 'Véhicules',
            'drivers' => 'Chauffeurs',
            'trips' => 'Courses',
            'payments' => 'Paiements',
            'notifications' => 'Notifications',
            'application' => 'Application',
            //'safety' => 'Sécurité',
            'geofencing' => 'Géofencing',
        ];

        // Ensure all categories exist in the settings array, even if empty
        foreach ($categories as $categoryKey => $categoryName) {
            if (!isset($settings[$categoryKey])) {
                $settings[$categoryKey] = collect();
            }
        }

        return view('settings.index', compact('settings', 'categories'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = AppSetting::where('key', $key)->first();
            
            if ($setting) {
                // Handle checkbox arrays
                if (is_array($value) && $setting->type === 'array') {
                    // Remove empty values from array
                    $value = array_filter($value);
                    $value = json_encode($value);
                }
                // Handle checkbox boolean values
                elseif ($setting->type === 'boolean') {
                    $value = $value === 'true' || $value === true || $value === '1' ? 'true' : 'false';
                }
                // Handle regular arrays
                elseif (is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
            } else {
                // Optionally create new setting if it doesn't exist
                // This allows for dynamic addition of settings
                AppSetting::create([
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'type' => $this->determineType($value),
                    'category' => $this->determineCategory($key),
                    'display_name' => ucfirst(str_replace('_', ' ', $key)),
                    'description' => 'Paramètre ajouté dynamiquement',
                    'sort_order' => 999,
                ]);
            }
        }

        // Clear settings cache
        Cache::forget('app_settings');

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function updateCategory(Request $request, $category)
    {
        $settings = $request->except('_token', '_method');
        
        foreach ($settings as $key => $value) {
            if (strpos($key, 'settings[') === 0) {
                // Extract the actual key name from settings[key_name]
                $actualKey = str_replace(['settings[', ']'], '', $key);
                
                $setting = AppSetting::where('key', $actualKey)
                    ->where('category', $category)
                    ->first();
                
                if ($setting) {
                    // Handle checkbox arrays
                    if (is_array($value) && $setting->type === 'array') {
                        $value = array_filter($value);
                        $value = json_encode($value);
                    }
                    // Handle checkbox boolean values
                    elseif ($setting->type === 'boolean') {
                        $value = $value === 'true' || $value === true || $value === '1' ? 'true' : 'false';
                    }
                    // Handle regular arrays
                    elseif (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $setting->update(['value' => $value]);
                }
            }
        }

        Cache::forget('app_settings');

        return redirect()->route('settings.index')
            ->with('success', "Paramètres {$category} mis à jour avec succès.");
    }

    public function resetToDefaults()
    {
        // Re-run the seeder
        $this->call(SettingsSeeder::class);
        
        Cache::forget('app_settings');

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres réinitialisés aux valeurs par défaut.');
    }

    public function getSetting($key)
    {
        return AppSetting::getValue($key);
    }

    public function getSettingsByCategory($category)
    {
        return AppSetting::getSettingsByCategory($category);
    }

    /**
     * Determine the type based on value
     */
    private function determineType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            if (is_float($value + 0)) {
                return 'decimal';
            } else {
                return 'integer';
            }
        } elseif (is_array($value)) {
            return 'array';
        } else {
            return 'string';
        }
    }

    /**
     * Determine category based on key name
     */
    private function determineCategory($key)
    {
        $categoryMap = [
            'pricing' => ['base_fare', 'cost_per_km', 'cost_per_minute', 'minimum_fare', 'night_', 'peak_', 'surcharge'],
            'commission' => ['commission', 'fee', 'bonus', 'referral'],
            'vehicles' => ['vehicle', 'multiplier', 'passenger', 'age_years'],
            'drivers' => ['driver', 'rating', 'acceptance', 'cancellation'],
            'trips' => ['trip', 'waiting', 'search', 'booking', 'stop'],
            'payments' => ['payment', 'wallet', 'currency', 'withdrawal', 'payout'],
            'notifications' => ['notification', 'sms', 'email', 'push'],
            'application' => ['app_', 'support', 'maintenance', 'debug', 'api', 'cache', 'session', 'timezone', 'language'],
            'safety' => ['sos', 'safety', 'verification', 'otp', 'audio', 'mask'],
            'geofencing' => ['geofencing', 'zone', 'gps', 'location', 'radius'],
        ];

        $key = strtolower($key);
        
        foreach ($categoryMap as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($key, $pattern) !== false) {
                    return $category;
                }
            }
        }

        return 'application'; // Default category
    }
}