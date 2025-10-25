<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TodoComment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'todo_id',
        'user_id',
        'content',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the todo that owns the comment.
     */
    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }

    /**
     * Get the user that wrote the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted content with mentions.
     */
    public function getFormattedContentAttribute(): string
    {
        // Simple mention parsing - in a real app, you'd want more sophisticated parsing
        $content = $this->content;
        
        // Convert @username mentions to links
        $content = preg_replace('/@(\w+)/', '<a href="#" class="mention">@$1</a>', $content);
        
        // Convert URLs to links
        $content = preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" rel="noopener">$1</a>', $content);
        
        // Convert line breaks to <br> tags
        $content = nl2br($content);
        
        return $content;
    }

    /**
     * Get the time ago string.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope a query to only include recent comments.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include comments by a specific user.
     */
    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
