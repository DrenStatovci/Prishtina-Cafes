<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Cafe;
use App\Models\Branch;

class StaffProfile extends Model
{
    /** @use HasFactory<\Database\Factories\StaffProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cafe_id',
        'branch_id',
        'position',
        'hire_date',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cafe(): BelongsTo
    {
        return $this->belongsTo(Cafe::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Create a staff profile for a cafe owner
     */
    public static function createForOwner(int $userId, int $cafeId): self
    {
        return self::create([
            'user_id' => $userId,
            'cafe_id' => $cafeId,
            'position' => 'owner',
            'is_active' => true,
            'hire_date' => now(),
        ]);
    }

    /**
     * Remove owner staff profile for a specific cafe
     */
    public static function removeOwnerFromCafe(int $userId, int $cafeId): bool
    {
        return self::where('user_id', $userId)
            ->where('cafe_id', $cafeId)
            ->where('position', 'owner')
            ->delete() > 0;
    }
}
