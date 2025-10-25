<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tag extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'user_id',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'color'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the tag.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the todos that have this tag.
     */
    public function todos()
    {
        return $this->belongsToMany(Todo::class, 'todo_tags');
    }

    /**
     * Get the completed todos that have this tag.
     */
    public function completedTodos()
    {
        return $this->todos()->completed();
    }

    /**
     * Get the pending todos that have this tag.
     */
    public function pendingTodos()
    {
        return $this->todos()->pending();
    }

    /**
     * Get the overdue todos that have this tag.
     */
    public function overdueTodos()
    {
        return $this->todos()->overdue();
    }

    /**
     * Get the todos count for this tag.
     */
    public function getTodosCountAttribute(): int
    {
        return $this->todos()->count();
    }

    /**
     * Get the completed todos count for this tag.
     */
    public function getCompletedTodosCountAttribute(): int
    {
        return $this->completedTodos()->count();
    }

    /**
     * Get the pending todos count for this tag.
     */
    public function getPendingTodosCountAttribute(): int
    {
        return $this->pendingTodos()->count();
    }

    /**
     * Get the overdue todos count for this tag.
     */
    public function getOverdueTodosCountAttribute(): int
    {
        return $this->overdueTodos()->count();
    }

    /**
     * Get the completion percentage for this tag.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $totalTodos = $this->todos_count;
        
        if ($totalTodos === 0) {
            return 0;
        }

        return round(($this->completed_todos_count / $totalTodos) * 100);
    }

    /**
     * Get the tag's statistics.
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'total' => $this->todos_count,
            'completed' => $this->completed_todos_count,
            'pending' => $this->pending_todos_count,
            'overdue' => $this->overdue_todos_count,
            'completion_percentage' => $this->completion_percentage,
        ];
    }

    /**
     * Scope a query to only include tags with todos.
     */
    public function scopeWithTodos($query)
    {
        return $query->has('todos');
    }

    /**
     * Scope a query to only include tags without todos.
     */
    public function scopeWithoutTodos($query)
    {
        return $query->doesntHave('todos');
    }

    /**
     * Get the most used tags for a user.
     */
    public function scopeMostUsed($query, User $user, int $limit = 10)
    {
        return $query->where('user_id', $user->id)
                    ->withCount('todos')
                    ->orderBy('todos_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Get the recently used tags for a user.
     */
    public function scopeRecentlyUsed($query, User $user, int $limit = 10)
    {
        return $query->where('user_id', $user->id)
                    ->whereHas('todos', function ($q) {
                        $q->where('updated_at', '>=', now()->subDays(30));
                    })
                    ->orderBy('updated_at', 'desc')
                    ->limit($limit);
    }
}
