<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    protected function related(User $user, Payment $payment): bool
    {
        $o = $payment->order;
        if ($user->hasRole('admin')) return true;
        if ($o->user_id && $o->user_id === $user->id) return true;

        return $user->isOwnerOfCafe($o->cafe_id)
            || $user->isStaffOfCafe($o->cafe_id)
            || ($o->branch_id && $user->isStaffOfBranch($o->branch_id));
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->related($user, $payment);
    }

    public function create(User $user, int $orderId): bool
    {
        // delegohet te OrderPolicy::pay
        $order = \App\Models\Order::find($orderId);
        return $order ? (new \App\Policies\OrderPolicy)->pay($user, $order) : false;
    }

    public function refund(User $user, Payment $payment): bool
    {
        // vetëm admin/owner/manager të kafesë
        $o = $payment->order;
        if ($user->hasRole('admin')) return true;
        return $user->isOwnerOfCafe($o->cafe_id) || ($user->isStaffOfCafe($o->cafe_id) && $user->hasAnyRole(['owner','manager']));
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin');
    }
}