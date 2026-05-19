<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon'];

    public const NAV_CACHE_KEY = 'categories.navigation';

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return Collection<int, self> */
    public static function forNavigation(): Collection
    {
        return Cache::remember(self::NAV_CACHE_KEY, 3600, fn () => static::orderBy('name')->get());
    }

    public static function flushNavigationCache(): void
    {
        Cache::forget(self::NAV_CACHE_KEY);
    }
}
