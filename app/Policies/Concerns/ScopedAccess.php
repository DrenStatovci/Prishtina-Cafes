<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ScopedAccess
{
    public function admin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function owner(User $user, int $cafe_id): bool
    {
        return $user->isOwnerOfCafe($cafe_id);
    }

    public function manageCafe(User $user, int $cafe_id): bool
    {
        return $user->canManageCafe($cafe_id);
    }

    public function manageBranch(User $user, int $branch_id): bool
    {
        return $user->canManageBranch($branch_id);
    }

    public function staffOfCafe(User $user, int $cafe_id)
    {
        return $user->isStaffOfCafe($cafe_id);
    }

    public function staffOfBranch(User $user, int $branch_id)
    {
        return $user->isStaffOfBranch($branch_id);
    }
}
