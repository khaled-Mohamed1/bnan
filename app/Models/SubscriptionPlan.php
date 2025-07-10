<?php

namespace App\Models;

use App\Enums\SubscriptionTier;
use App\Enums\SubscriptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'device_limit',
        'user_limit',
        'subscription_type',
        'subscription_tier',
        'trial_period_days',
        'is_active',
        'max_dashboards',
        'data_retention',
        'api_access',
        'white_labeling',
        'custom_widgets',
        'email_alerts',
        'sms_integration',
        'role_management',
        'rule_engine',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'data_retention' => 'boolean',
        'api_access' => 'boolean',
        'white_labeling' => 'boolean',
        'email_alerts' => 'boolean',
        'sms_integration' => 'boolean',
        'role_management' => 'boolean',
        'rule_engine' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'subscription_type' => SubscriptionType::class,
        'subscription_tier' => SubscriptionTier::class,
    ];

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Check if the plan is currently assigned to active users.
     */
    public function isInUse(): bool
    {
        return $this->userSubscriptions()
            ->where('status', 'active')
            ->exists();
    }
}
