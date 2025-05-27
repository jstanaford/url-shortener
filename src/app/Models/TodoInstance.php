<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoInstance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'todo_id',
        'due_date',
        'complete',
        'instance_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'complete' => 'boolean',
    ];

    /**
     * Get the todo that owns the instance.
     */
    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }
}
