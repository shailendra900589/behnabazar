<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image', 'link', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];
}
