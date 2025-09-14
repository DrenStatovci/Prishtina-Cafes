<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PaymentStatus;
use App\Models\User;
use App\Models\Cafe;
use App\Models\Branch;
use App\Models\OrderItem;
use App\Models\Payment;


class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cafe_id',
        'branch_id',
        'status',
        'payment_status',
        'total_price'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public const STATUSES = ['pending', 'preparing', 'ready', 'delivered', 'cancelled'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cafe(): BelongsTo
    {
        return $this->belongsTo(Cafe::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function totalPaid(): string
    {
        return (string) $this->payments()
            ->where('status', PaymentStatus::SUCCEEDED->value)
            ->sum('amount');
    }

    public function refreshPaymentStatus(): void
    {
        $paid = $this->totalPaid();

        $this->payment_status = bccomp($paid, $this->total_price, 2) >= 0 ? 'paid' : 'unpaid';

        $this->save();
    }
}
