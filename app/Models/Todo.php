<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Todo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'user_id',
        'list_id',
        'parent_id',
        'sort_order',
        'estimated_time',
        'actual_time',
        'is_recurring',
        'recurring_pattern',
        'recurring_interval',
        'recurring_end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'recurring_end_date' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'due_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the todo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the list that the todo belongs to.
     */
    public function list()
    {
        return $this->belongsTo(TodoList::class, 'list_id');
    }

    /**
     * Get the parent todo (for subtasks).
     */
    public function parent()
    {
        return $this->belongsTo(Todo::class, 'parent_id');
    }

    /**
     * Get the subtasks of the todo.
     */
    public function subtasks()
    {
        return $this->hasMany(Todo::class, 'parent_id');
    }

    /**
     * Get the tags for the todo.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'todo_tags');
    }

    /**
     * Get the attachments for the todo.
     */
    public function attachments()
    {
        return $this->hasMany(TodoAttachment::class);
    }

    /**
     * Get the comments for the todo.
     */
    public function comments()
    {
        return $this->hasMany(TodoComment::class);
    }

    /**
     * Scope a query to only include completed todos.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending todos.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in-progress todos.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include overdue todos.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'completed');
    }

    /**
     * Scope a query to only include todos due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope a query to only include todos due this week.
     */
    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query to only include high priority todos.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Mark the todo as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the todo as pending.
     */
    public function markAsPending(): void
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Check if the todo is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the todo is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !$this->isCompleted();
    }

    /**
     * Check if the todo is due today.
     */
    public function isDueToday(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    /**
     * Get the completion percentage for subtasks.
     */
    public function getCompletionPercentageAttribute(): int
    {
        if ($this->subtasks()->count() === 0) {
            return $this->isCompleted() ? 100 : 0;
        }

        $completedSubtasks = $this->subtasks()->completed()->count();
        $totalSubtasks = $this->subtasks()->count();

        return $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
    }

    /**
     * Get the priority color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'in_progress' => 'blue',
            'cancelled' => 'red',
            'pending' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the formatted due date.
     */
    public function getFormattedDueDateAttribute(): ?string
    {
        if (!$this->due_date) {
            return null;
        }

        if ($this->due_date->isToday()) {
            return 'Today at ' . $this->due_date->format('g:i A');
        }

        if ($this->due_date->isTomorrow()) {
            return 'Tomorrow at ' . $this->due_date->format('g:i A');
        }

        if ($this->due_date->isYesterday()) {
            return 'Yesterday at ' . $this->due_date->format('g:i A');
        }

        return $this->due_date->format('M j, Y g:i A');
    }

    /**
     * Get the time spent in a human-readable format.
     */
    public function getTimeSpentAttribute(): string
    {
        if (!$this->actual_time) {
            return '0 minutes';
        }

        $hours = floor($this->actual_time / 60);
        $minutes = $this->actual_time % 60;

        if ($hours > 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . 
                   ($minutes > 0 ? ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '');
        }

        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }
}
