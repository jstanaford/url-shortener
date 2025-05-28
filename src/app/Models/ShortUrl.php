<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrl extends Model
{
    protected $fillable = [
        'og_url',
        'short_uri',
        'og_url_hash',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->og_url_hash) && !empty($model->og_url)) {
                $model->og_url_hash = md5($model->og_url);
            }
        });
    }

    /**
     * Get the views for the short URL.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ShortUrlView::class, 'short_uri', 'short_uri');
    }
}
