<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Todo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'todo_title',
        'details',
        'due_date',
        'recurring',
        'recurring_schedule',
        'complete',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'recurring' => 'boolean',
        'complete' => 'boolean',
    ];

    /**
     * Get the user that owns the todo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the todo.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the instances for the todo.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(TodoInstance::class);
    }

    /**
     * Get the meta entries for the todo.
     */
    public function meta(): HasMany
    {
        return $this->hasMany(TodoMeta::class);
    }
}
