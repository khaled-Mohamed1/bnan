<?php

namespace App\Enums;

enum SubscriptionTier: string
{
    case Free = 'Free';
    case Basic = 'Basic';
    case Pro = 'Pro';
    case Enterprise = 'Enterprise';
}
