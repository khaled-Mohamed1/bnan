<?php

namespace App\Enums;

enum SubscriptionType: string
{
    case Weekly = 'Weekly';
    case Monthly = 'Monthly';
    case Annual = 'Annual';
}
