<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\Concerns\ScopedAccess;

class BranchPolicy
{
    use ScopedAccess;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // return $user->hasAnyRole(['admin','owner','manager','waiter','bartender']);
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ?Branch $branch): bool
    {
        //  return $this->admin($user)
        //     || $user->isOwnerOfCafe($branch->cafe_id)
        //     || $this->staffOfBranch($user, $branch->id)
        //     || $this->staffOfCafe($user, $branch->cafe_id);
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $cafe_id): bool
    {
        return $this->admin($user) || $this->manageCafe($user, $cafe_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $this->admin($user) || $this->manageBranch($user, $branch->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $this->admin($user) || $user->isOwnerOfCafe($branch->cafe_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Branch $branch): bool
    {
        return $this->admin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return $this->admin($user);
    }
}
