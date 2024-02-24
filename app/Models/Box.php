<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Box extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

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

    /**
     * The boxes that belong to the box.
     */
    public function parentBoxes(): BelongsToMany
    {
        return $this->belongsToMany(Box::class, 'box_box', 'box_id', 'pack_id')->withTimestamps();
    }

    /**
     * The kinds that belong to the box.
     */
    public function kinds(): BelongsToMany
    {
        return $this->belongsToMany(Kind::class, 'box_kind')->withTimestamps();
    }

    /**
     * The celebrities that belong to the box.
     */
    public function celebrities(): BelongsToMany
    {
        return $this->belongsToMany(Celebrity::class)->withPivot('job')->withTimestamps();
    }

    /**
     * The directors that belong to the box.
     */
    public function directors(): BelongsToMany
    {
        return $this->celebrities()->wherePivotIn('job', ['Réalisateur']);
    }

    /**
     * The actors that belong to the box.
     */
    public function actors(): BelongsToMany
    {
        return $this->celebrities()->wherePivotIn('job', ['Acteur', 'Voix en VO', 'Voix en VF']);
    }

    /**
     * The scriptwriters that belong to the box.
     */
    public function scriptwriters(): BelongsToMany
    {
        return $this->celebrities()->wherePivotIn('job', ['Scénariste']);
    }

    /**
     * The composers that belong to the box.
     */
    public function composers(): BelongsToMany
    {
        return $this->celebrities()->wherePivotIn('job', ['Compositeur']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('thumbnail')
            ->fit(Manipulations::FIT_CONTAIN, 150, 150)
            ->nonQueued();
    }
}