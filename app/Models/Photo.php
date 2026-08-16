<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    protected $fillable = ['path', 'position'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(PhotoAlbum::class, 'photo_album_id');
    }
}
