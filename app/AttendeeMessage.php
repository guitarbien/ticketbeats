<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    public function recipients(): Collection
    {
        return $this->concert->orders()->pluck('email');
    }
}
