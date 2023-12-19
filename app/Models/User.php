<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The boxes that belong to the user.
     */
    public function boxes(): BelongsToMany
    {
        return $this->belongsToMany(Box::class)->withTimestamps();
    }

    /**
     * The boxes that belong to the user.
     */
    public function movies()
    {
        /*
            select boxes.*
            from box_user 
            inner join box_box ON box_user.box_id = box_box.pack_id
            inner join boxes ON boxes.id = box_box.box_id
            union
            select boxes.*
            from box_user 
            inner join boxes ON boxes.id = box_user.box_id 
            where box_user.box_id NOT IN (SELECT box_box.pack_id FROM box_box);

            select box_box.box_id
            from box_user 
            inner join box_box ON box_user.box_id = box_box.pack_id
            union
            select boxes.id
            from box_user 
            inner join boxes ON boxes.id = box_user.box_id 
            where box_user.box_id NOT IN (SELECT box_box.pack_id FROM box_box);
        */

        // Get simple boxes
        $query_simple_boxes = DB::table('box_user')
            ->select('boxes.id as box_id')
            ->join('boxes', 'boxes.id', '=', 'box_user.box_id')
            ->whereNotIn('box_user.box_id', function ($query) {
                $query->select('box_box.pack_id')->from('box_box');
            })
            ->where('box_user.user_id', $this->id);

        // Get boxes in packs
        $query_boxes_in_packs = DB::table('box_user')
            ->select('box_box.box_id as box_id')
            ->join('box_box', 'box_user.box_id', '=', 'box_box.pack_id')
            ->where('box_user.user_id', $this->id);

        $subquery = $query_simple_boxes->union($query_boxes_in_packs);

        return Box::whereIn('id', function ($query) use ($subquery) {
            $query->select('sub.box_id')->fromSub($subquery, 'sub');
        });
    }
}
