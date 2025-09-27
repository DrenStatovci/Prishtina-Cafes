<?php

namespace App\Policies;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StaffProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin','owner','manager']);
    }

    public function view(User $user, StaffProfile $profile): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($profile->user_id === $user->id) return true; // sheh profilin e vet

        return $user->isOwnerOfCafe($profile->cafe_id)
            || ($user->isStaffOfCafe($profile->cafe_id) && $user->hasAnyRole(['owner','manager']));
    }

    public function create(User $user, int $cafeId): bool
    {
        return $user->hasRole('admin') || $user->isOwnerOfCafe($cafeId) || ($user->isStaffOfCafe($cafeId) && $user->hasAnyRole(['owner','manager']));
    }

    public function update(User $user, StaffProfile $profile): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($profile->user_id === $user->id) return true;

        return $user->isOwnerOfCafe($profile->cafe_id)
            || ($user->isStaffOfCafe($profile->cafe_id) && $user->hasAnyRole(['owner','manager']));
    }

    public function delete(User $user, StaffProfile $profile): bool
    {
        return $user->hasRole('admin') || $user->isOwnerOfCafe($profile->cafe_id);
    }
}