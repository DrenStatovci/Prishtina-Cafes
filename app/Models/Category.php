<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Cafe;
// use App\Models\Product;


class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'cafe_id',
        'name',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active'
    ];


    public function cafe(): BelongsTo
    {
        return $this->belongsTo(Cafe::class);
    }

    // public function products(): HasMany{
    //     return $this->hasMany(Product::class);
    // }
}
