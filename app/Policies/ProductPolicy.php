<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user, int $cafeId): bool
    {
        return $user->isAdmin() || $user->canManageCafe($cafeId);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->canManageCafe($product->cafe_id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->canManageCafe($product->cafe_id);
    }
}
