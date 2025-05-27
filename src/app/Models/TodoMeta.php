<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoMeta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'todo_id',
        'meta_key',
        'meta_value',
    ];

    /**
     * Get the todo that owns the meta.
     */
    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }
}
