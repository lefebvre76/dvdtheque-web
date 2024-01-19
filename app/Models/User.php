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
        return $this->belongsToMany(Box::class)->withTimestamps()->withPivot('wishlist');
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
            ->where('box_user.user_id', $this->id)
            ->where('box_user.wishlist', false);

        // Get boxes in packs
        $query_boxes_in_packs = DB::table('box_user')
            ->select('box_box.box_id as box_id')
            ->join('box_box', 'box_user.box_id', '=', 'box_box.pack_id')
            ->where('box_user.user_id', $this->id)
            ->where('box_user.wishlist', false);

        $subquery = $query_simple_boxes->union($query_boxes_in_packs);

        return Box::whereIn('id', function ($query) use ($subquery) {
            $query->select('sub.box_id')->fromSub($subquery, 'sub');
        });
    }

    public function favoriteKinds($limit = 3) 
    {
        if ($this->movies()->count() < 1) {
            return collect([]);
        }
        return DB::table('box_kind')
            ->select('kinds.id', 'kinds.name', DB::raw('count(*) as total'))
            ->whereIn('box_id', $this->movies()->select('id')->pluck('id'))
            ->join('kinds', 'kinds.id', '=', 'kind_id')
            ->groupBy('kinds.id', 'kinds.name')
            ->orderBy('total', 'DESC')
            ->limit($limit)
            ->get();
    } 

    public function favoriteCelebrities($jobs, $limit = 3) 
    {
        if ($this->movies()->count() < 1) {
            return collect([]);
        }
        return DB::table('box_celebrity')
            ->select('celebrities.id', 'celebrities.name', DB::raw('count(*) as total'))
            ->whereIn('box_id', $this->movies()->select('id')->pluck('id'))
            ->whereIn('box_celebrity.job', $jobs)
            ->join('celebrities', 'celebrities.id', '=', 'celebrity_id')
            ->groupBy('celebrities.id', 'celebrities.name')
            ->orderBy('total', 'DESC')
            ->limit($limit)
            ->get();
    } 
}
