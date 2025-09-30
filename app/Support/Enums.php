<?php

namespace App\Support;

class Enums {
    public const ORDER_STATUS = ['pending','preparing','ready','delivered','cancelled'];
    public const PAYMENT_STATUS = ['unpaid','paid','refunded'];
    public const PAYMENT_PREF = ['cash','card','online'];
}