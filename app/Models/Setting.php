<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Retrieve a setting value (cached forever until busted).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    /**
     * Set a single setting value and bust cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Bulk-set many settings at once.
     */
    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }

    /**
     * Retrieve multiple settings as associative array.
     */
    public static function getMany(array $keys, array $defaults = []): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = static::get($key, $defaults[$key] ?? null);
        }
        return $result;
    }
}
