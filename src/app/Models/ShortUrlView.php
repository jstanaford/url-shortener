<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrlView extends Model
{
    protected $fillable = [
        'short_uri',
        'time_visited',
    ];

    /**
     * Get the short URL that this view belongs to.
     */
    public function shortUrl(): BelongsTo
    {
        return $this->belongsTo(ShortUrl::class, 'short_uri', 'short_uri');
    }
}
