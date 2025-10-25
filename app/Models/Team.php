<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Team extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the owner of the team.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the members of the team.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot(['role', 'invited_by', 'invited_at', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the admins of the team.
     */
    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    /**
     * Get the regular members of the team.
     */
    public function regularMembers()
    {
        return $this->members()->wherePivot('role', 'member');
    }

    /**
     * Get the viewers of the team.
     */
    public function viewers()
    {
        return $this->members()->wherePivot('role', 'viewer');
    }

    /**
     * Get the todo lists for the team.
     */
    public function todoLists()
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * Get the todos for the team.
     */
    public function todos()
    {
        return $this->hasManyThrough(Todo::class, TodoList::class);
    }

    /**
     * Check if a user is a member of the team.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user is the owner of the team.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Check if a user is an admin of the team.
     */
    public function isAdmin(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Get the role of a user in the team.
     */
    public function getUserRole(User $user): ?string
    {
        if ($this->isOwner($user)) {
            return 'owner';
        }

        $member = $this->members()->where('user_id', $user->id)->first();
        return $member ? $member->pivot->role : null;
    }

    /**
     * Add a member to the team.
     */
    public function addMember(User $user, string $role = 'member', ?User $invitedBy = null): void
    {
        $this->members()->attach($user->id, [
            'role' => $role,
            'invited_by' => $invitedBy?->id,
            'invited_at' => now(),
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Update a member's role.
     */
    public function updateMemberRole(User $user, string $role): void
    {
        $this->members()->updateExistingPivot($user->id, [
            'role' => $role,
        ]);
    }

    /**
     * Get the team's statistics.
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'members_count' => $this->members()->count(),
            'lists_count' => $this->todoLists()->count(),
            'todos_count' => $this->todos()->count(),
            'completed_todos_count' => $this->todos()->completed()->count(),
        ];
    }

    /**
     * Get the team's activity.
     */
    public function getActivityAttribute(): array
    {
        return [
            'recent_todos' => $this->todos()
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(),
            'recent_lists' => $this->todoLists()
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Check if the team is active.
     */
    public function isActive(): bool
    {
        return $this->members()->count() > 0;
    }

    /**
     * Get the team's completion percentage.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $totalTodos = $this->todos()->count();
        
        if ($totalTodos === 0) {
            return 0;
        }

        $completedTodos = $this->todos()->completed()->count();
        return round(($completedTodos / $totalTodos) * 100);
    }
}
