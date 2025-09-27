<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderItemPolicy
{
    protected function canTouch(User $user, OrderItem $item): bool
    {
        $o = $item->order;
        if ($user->hasRole('admin')) return true;
        if ($o->user_id && $o->user_id === $user->id) return true;

        return $user->isOwnerOfCafe($o->cafe_id)
            || $user->isStaffOfCafe($o->cafe_id)
            || ($o->branch_id && $user->isStaffOfBranch($o->branch_id));
    }

    public function view(User $user, OrderItem $item): bool
    {
        return $this->canTouch($user, $item);
    }

    public function create(User $user, int $orderId): bool
    {
        // Lejo kur përdoruesi mund të prekë porosinë dhe porosia s’ka hyrë në “preparing+”
        $order = \App\Models\Order::find($orderId);
        if (! $order) return false;

        $can = (new self)->canTouch($user, new OrderItem(['order_id'=>$order->id]));
        return $can && in_array($order->status, ['pending']);
    }

    public function update(User $user, OrderItem $item): bool
    {
        $o = $item->order;
        return $this->canTouch($user, $item) && in_array($o->status, ['pending']);
    }

    public function delete(User $user, OrderItem $item): bool
    {
        $o = $item->order;
        return $this->canTouch($user, $item) && in_array($o->status, ['pending']);
    }
}