<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the value as a specific type.
     */
    public function getValueAs(string $type)
    {
        return match($type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array' => json_decode($this->value, true),
            'object' => json_decode($this->value),
            default => $this->value,
        };
    }

    /**
     * Set the value with type conversion.
     */
    public function setValueAttribute($value): void
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = (string) $value;
        }
    }

    /**
     * Scope a query to only include preferences with a specific key.
     */
    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to only include preferences with a specific value.
     */
    public function scopeValue($query, $value)
    {
        return $query->where('value', $value);
    }
}
