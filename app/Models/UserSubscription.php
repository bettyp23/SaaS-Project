<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancelled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->current_period_end > now();
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if the subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if the subscription is unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    /**
     * Check if the subscription is in trial.
     */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at > now();
    }

    /**
     * Check if the subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->current_period_end < now();
    }

    /**
     * Get the days remaining in the current period.
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->current_period_end->diffInDays(now());
    }

    /**
     * Get the days remaining in the trial.
     */
    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->isInTrial()) {
            return 0;
        }

        return $this->trial_ends_at->diffInDays(now());
    }

    /**
     * Get the subscription status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'cancelled' => 'red',
            'past_due' => 'orange',
            'unpaid' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the subscription status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'cancelled' => 'Cancelled',
            'past_due' => 'Past Due',
            'unpaid' => 'Unpaid',
            default => 'Unknown',
        };
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Reactivate the subscription.
     */
    public function reactivate(): void
    {
        $this->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);
    }

    /**
     * Get the next billing date.
     */
    public function getNextBillingDateAttribute(): ?Carbon
    {
        if ($this->isCancelled() || $this->isExpired()) {
            return null;
        }

        return $this->current_period_end;
    }

    /**
     * Get the subscription summary.
     */
    public function getSummaryAttribute(): array
    {
        return [
            'status' => $this->status_label,
            'status_color' => $this->status_color,
            'plan_name' => $this->plan->name,
            'is_active' => $this->isActive(),
            'is_cancelled' => $this->isCancelled(),
            'is_in_trial' => $this->isInTrial(),
            'is_expired' => $this->isExpired(),
            'days_remaining' => $this->days_remaining,
            'trial_days_remaining' => $this->trial_days_remaining,
            'next_billing_date' => $this->next_billing_date,
        ];
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('current_period_end', '<', now());
    }

    /**
     * Scope a query to only include subscriptions in trial.
     */
    public function scopeInTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
    }
}
