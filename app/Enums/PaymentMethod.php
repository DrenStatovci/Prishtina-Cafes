<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MOCK   = 'mock';
    case CASH   = 'cash';
    case CARD   = 'card';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
}
