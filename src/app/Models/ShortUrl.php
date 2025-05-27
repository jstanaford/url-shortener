<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrl extends Model
{
    protected $fillable = [
        'og_url',
        'short_uri',
    ];

    /**
     * Get the views for the short URL.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ShortUrlView::class, 'short_uri', 'short_uri');
    }
}
