<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cafe;
use App\Policies\Concerns\ScopedAccess;

class CafePolicy
{
    use ScopedAccess;

    //qito dy tparat po i lo qishtu se spo di hala qka ndodh kur dojn klientat normal me i pa kafiqat.

    public function viewAny(User $user): bool
    {
        // return $user->hasAnyRole(['admin','owner','manager']);
        return true;
    }

    public function view(User $user, Cafe $cafe): bool
    {
        // return $this->admin($user) || $user->isOwnerOfCafe($cafe->id) || $this->staffOfCafe($user, $cafe->id);
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'owner']);
    }

    public function update(User $user, Cafe $cafe): bool
    {
        return $this->admin($user) || $user->isOwnerOfCafe($cafe->id) || $user->canManageCafe($cafe->id);
    }

    public function delete(User $user, Cafe $cafe): bool
    {
        return $this->admin($user) || $user->isOwnerOfCafe($cafe->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cafe $cafe): bool
    {
        return $this->admin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cafe $cafe): bool
    {
        return $this->admin($user);
    }
}
