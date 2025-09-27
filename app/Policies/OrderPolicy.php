<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($order->user_id && $order->user_id === $user->id) return true;

        return $user->isOwnerOfCafe($order->cafe_id)
            || $user->isStaffOfCafe($order->cafe_id)
            || ($order->branch_id && $user->isStaffOfBranch($order->branch_id));
    }

    public function create(User $user): bool
    {
        return $user->exists; // çdo i autentikuar
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($order->branch_id && $user->isStaffOfBranch($order->branch_id)) return true;
        return $user->canManageCafe($order->cafe_id);
    }

    public function pay(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($order->user_id && $order->user_id === $user->id) return true;
        if ($order->branch_id && $user->isStaffOfBranch($order->branch_id)) return true;
        return $user->isStaffOfCafe($order->cafe_id);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasRole('admin') || $user->isOwnerOfCafe($order->cafe_id);
    }
}