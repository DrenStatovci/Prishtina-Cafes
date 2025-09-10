<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Cafe;

class Branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasFactory;
    
    protected $fillable = [
        'cafe_id',
        'name',
        'slug',
        'address',
        'phone',
        'opening_hours',
        'is_active',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'is_active' => 'boolean',
    ];

    public function cafe(): BelongsTo
    {
        return $this->belongsTo(Cafe::class);
    }
}
