<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TodoList extends Model
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
        'color',
        'user_id',
        'team_id',
        'is_public',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'color', 'is_public'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the list.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that the list belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the todos for the list.
     */
    public function todos()
    {
        return $this->hasMany(Todo::class, 'list_id');
    }

    /**
     * Get the completed todos for the list.
     */
    public function completedTodos()
    {
        return $this->todos()->completed();
    }

    /**
     * Get the pending todos for the list.
     */
    public function pendingTodos()
    {
        return $this->todos()->pending();
    }

    /**
     * Get the in-progress todos for the list.
     */
    public function inProgressTodos()
    {
        return $this->todos()->inProgress();
    }

    /**
     * Get the overdue todos for the list.
     */
    public function overdueTodos()
    {
        return $this->todos()->overdue();
    }

    /**
     * Get the completion percentage for the list.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $totalTodos = $this->todos()->count();
        
        if ($totalTodos === 0) {
            return 0;
        }

        $completedTodos = $this->completedTodos()->count();
        return round(($completedTodos / $totalTodos) * 100);
    }

    /**
     * Get the total todos count.
     */
    public function getTotalTodosAttribute(): int
    {
        return $this->todos()->count();
    }

    /**
     * Get the completed todos count.
     */
    public function getCompletedTodosAttribute(): int
    {
        return $this->completedTodos()->count();
    }

    /**
     * Get the pending todos count.
     */
    public function getPendingTodosAttribute(): int
    {
        return $this->pendingTodos()->count();
    }

    /**
     * Get the overdue todos count.
     */
    public function getOverdueTodosAttribute(): int
    {
        return $this->overdueTodos()->count();
    }

    /**
     * Check if the list is empty.
     */
    public function isEmpty(): bool
    {
        return $this->todos()->count() === 0;
    }

    /**
     * Check if the list is completed.
     */
    public function isCompleted(): bool
    {
        return $this->todos()->count() > 0 && $this->pendingTodos()->count() === 0;
    }

    /**
     * Get the list's progress status.
     */
    public function getProgressStatusAttribute(): string
    {
        if ($this->isEmpty()) {
            return 'empty';
        }

        if ($this->isCompleted()) {
            return 'completed';
        }

        if ($this->overdueTodos()->count() > 0) {
            return 'overdue';
        }

        return 'in_progress';
    }

    /**
     * Get the list's progress color.
     */
    public function getProgressColorAttribute(): string
    {
        return match($this->progress_status) {
            'completed' => 'green',
            'overdue' => 'red',
            'in_progress' => 'blue',
            'empty' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope a query to only include public lists.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include private lists.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope a query to only include team lists.
     */
    public function scopeTeam($query)
    {
        return $query->whereNotNull('team_id');
    }

    /**
     * Scope a query to only include personal lists.
     */
    public function scopePersonal($query)
    {
        return $query->whereNull('team_id');
    }

    /**
     * Get the list's statistics.
     */
    public function getStatisticsAttribute(): array
    {
        return [
            'total' => $this->total_todos,
            'completed' => $this->completed_todos,
            'pending' => $this->pending_todos,
            'overdue' => $this->overdue_todos,
            'completion_percentage' => $this->completion_percentage,
            'progress_status' => $this->progress_status,
        ];
    }
}
