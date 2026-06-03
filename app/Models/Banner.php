<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image', 'link', 'sort_order', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function imageUrl(): string
    {
        return PublicStorage::url($this->image);
    }
}
