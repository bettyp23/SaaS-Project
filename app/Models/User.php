<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'profile_picture',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'timezone'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the todos for the user.
     */
    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    /**
     * Get the todo lists for the user.
     */
    public function todoLists()
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * Get the tags for the user.
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Get the teams owned by the user.
     */
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Get the teams the user is a member of.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot(['role', 'invited_by', 'invited_at', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the user's preferences.
     */
    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Get the user's subscription.
     */
    public function subscription()
    {
        return $this->hasOne(UserSubscription::class);
    }

    /**
     * Check if the user is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock the user account.
     */
    public function lock(int $minutes = 15): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Unlock the user account.
     */
    public function unlock(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
        
        if ($this->failed_login_attempts >= 5) {
            $this->lock();
        }
    }

    /**
     * Reset failed login attempts.
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update(['failed_login_attempts' => 0]);
    }

    /**
     * Get a preference value.
     */
    public function getPreference(string $key, $default = null)
    {
        $preference = $this->preferences()->where('key', $key)->first();
        return $preference ? $preference->value : $default;
    }

    /**
     * Set a preference value.
     */
    public function setPreference(string $key, $value): void
    {
        $this->preferences()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if user has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && 
               $this->subscription->status === 'active' && 
               $this->subscription->current_period_end > now();
    }

    /**
     * Get user's subscription plan.
     */
    public function getSubscriptionPlan()
    {
        return $this->subscription?->plan;
    }

    /**
     * Check if user can create more todos.
     */
    public function canCreateTodo(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return $this->todos()->count() < 50; // Free tier limit
        }

        $plan = $this->getSubscriptionPlan();
        return !$plan || !$plan->max_todos || $this->todos()->count() < $plan->max_todos;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }
}
