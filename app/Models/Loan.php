<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    const TYPE_LOAN = 'LOAN';
    const TYPE_BORROW = 'BORROW';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'box_id',
        'box_parent_id',
        'type',
        'contact',
        'contact_informations',
        'reminder',
        'comment'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reminder' => 'datetime',
        'contact_informations' => 'array'
    ];

    /**
     * Get the box that owns the loan.
     */
    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    /**
     * Get the parent box that owns the loan.
     */
    public function parentBox(): BelongsTo
    {
        return $this->belongsTo(Box::class, 'box_parent_id');
    }
}