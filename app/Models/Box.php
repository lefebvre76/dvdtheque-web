<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Box extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'bar_code',
        'title',
        'original_title',
        'year',
        'synopsis',
        'edition',
        'editor',
        'dvdfr_id',
    ];

    /**
     * The users that belong to the box.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * The boxes that belong to the box.
     */
    public function boxes(): BelongsToMany
    {
        return $this->belongsToMany(Box::class, 'box_box', 'pack_id', 'box_id')->withTimestamps();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('thumbnail')
            ->fit(Manipulations::FIT_CONTAIN, 150, 150)
            ->nonQueued();
    }
}