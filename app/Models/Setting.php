<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['setting_key', 'setting_value'];

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::where('setting_key', $key)->value('setting_value') ?? $default;
    }
}
