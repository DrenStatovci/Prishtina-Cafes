<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;





class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'payload',
        'processed_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'method' => PaymentMethod::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED;
    }
}
