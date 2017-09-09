<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * App\AttendeeMessage
 *
 * @mixin \Eloquent
 */
class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function orders(): Builder
    {
        return $this->concert->orders();
    }
}
