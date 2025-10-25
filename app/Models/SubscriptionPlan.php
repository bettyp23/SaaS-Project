<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'interval',
        'trial_days',
        'max_todos',
        'max_team_members',
        'features',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    /**
     * Get the active subscriptions for this plan.
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Check if the plan is free.
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Check if the plan is paid.
     */
    public function isPaid(): bool
    {
        return $this->price > 0;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get the price per month.
     */
    public function getPricePerMonthAttribute(): float
    {
        if ($this->interval === 'yearly') {
            return $this->price / 12;
        }

        return $this->price;
    }

    /**
     * Get the formatted price per month.
     */
    public function getFormattedPricePerMonthAttribute(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        $pricePerMonth = $this->price_per_month;
        return $this->currency . ' ' . number_format($pricePerMonth, 2) . '/month';
    }

    /**
     * Check if the plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get the plan's features as a formatted list.
     */
    public function getFeaturesListAttribute(): array
    {
        $featureLabels = [
            'basic_todos' => 'Basic todo management',
            'unlimited_todos' => 'Unlimited todos',
            'one_list' => 'One todo list',
            'multiple_lists' => 'Multiple todo lists',
            'team_collaboration' => 'Team collaboration',
            'file_attachments' => 'File attachments',
            'priority_support' => 'Priority support',
            'email_support' => 'Email support',
            'advanced_analytics' => 'Advanced analytics',
            'api_access' => 'API access',
            'white_label' => 'White label options',
            'dedicated_support' => 'Dedicated support',
        ];

        return array_map(function ($feature) use ($featureLabels) {
            return $featureLabels[$feature] ?? $feature;
        }, $this->features ?? []);
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include free plans.
     */
    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    /**
     * Scope a query to only include paid plans.
     */
    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }

    /**
     * Scope a query to only include monthly plans.
     */
    public function scopeMonthly($query)
    {
        return $query->where('interval', 'monthly');
    }

    /**
     * Scope a query to only include yearly plans.
     */
    public function scopeYearly($query)
    {
        return $query->where('interval', 'yearly');
    }
}
