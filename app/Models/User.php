<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Cafe;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cafes(): HasMany
    {
        return $this->hasMany(Cafe::class, 'owner_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOwnerOfCafe(int $cafe_id): bool
    {
        return Cafe::query()->where('id', $cafe_id)->where('owner_id', $this->id)->exists();
    }

    public function isStaffOfCafe(int $cafe_id): bool
    {
        return $this->staffProfiles()
            ->where('cafe_id', $cafe_id)
            ->where('is_active', true)
            ->exists();
    }

    public function isStaffOfBranch(int $branch_id): bool
    {
        return $this->staffProfiles()
            ->where('branch_id', $branch_id)
            ->where('is_active', true)
            ->exists();
    }

    public function canManageCafe(int $cafe_id): bool
    {
        if ($this->isAdmin() || $this->isOwnerOfCafe($cafe_id)) return true;

        if ($this->isStaffOfCafe($cafe_id) && $this->hasAnyRole('owner', 'manager')) return true;

        return false;
    }


    public function canManageBranch(int $branch_id): bool
    {
        if ($this->isAdmin()) return true;

        $branch = Branch::find($branch_id);
        if (!$branch) return false;

        if ($this->isOwnerOfCafe($branch->cafe_id)) return true;

        if ($this->isStaffOfCafe($branch->cafe_id) && $this->hasAnyRole(['owner', 'manager'])) return true;

        if ($this->isStaffOfBranch($branch_id) && $this->hasAnyRole(['owner', 'manager', 'waiter', 'bartender'])) return true;

        return false;
    }
}
