<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'effective_device_limit',
        'effective_user_limit',
        'effective_max_dashboards',
        'effective_custom_widgets',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Accessor for the effective device limit.
     * Returns the overridden value if set, otherwise the plan's default.
     */
    public function getDeviceLimitAttribute(): int
    {
        return $this->effective_device_limit ?? $this->plan->device_limit;
    }

    /**
     * Accessor for the effective user limit.
     */
    public function getUserLimitAttribute(): int
    {
        return $this->effective_user_limit ?? $this->plan->user_limit;
    }

    /**
     * Accessor for the effective max dashboards limit.
     */
    public function getMaxDashboardsAttribute(): int
    {
        return $this->effective_max_dashboards ?? $this->plan->max_dashboards;
    }

    /**
     * Accessor for the effective custom widgets limit.
     */
    public function getCustomWidgetsAttribute(): int
    {
        return $this->effective_custom_widgets ?? $this->plan->custom_widgets;
    }
}
