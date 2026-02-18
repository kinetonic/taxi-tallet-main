<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'display_name',
        'description',
        'options',
        'sort_order',
        'is_visible'
    ];

    protected $casts = [
        'options' => 'array',
        'is_visible' => 'boolean'
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? self::castValue($setting->value, $setting->type) : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value)
    {
        $setting = static::where('key', $key)->firstOrFail();
        $setting->value = $value;
        $setting->save();
        return $setting;
    }

    /**
     * Cast value based on type
     */
    private static function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer', 'number' => (int) $value,
            'float', 'decimal' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => json_decode($value, true),
            default => (string) $value,
        };
    }

    /**
     * Get all settings grouped by category
     */
    public static function getGroupedSettings()
    {
        return static::where('is_visible', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
    }

    /**
     * Get settings by category
     */
    public static function getSettingsByCategory($category)
    {
        return static::where('category', $category)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }
}