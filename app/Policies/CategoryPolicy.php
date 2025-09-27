<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user, int $cafeId): bool
    {
        return $user->isAdmin() || $user->canManageCafe($cafeId);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->canManageCafe($category->cafe_id);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->canManageCafe($category->cafe_id);
    }
}
