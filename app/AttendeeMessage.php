<?php

namespace App;

use Closure;
use Illuminate\Database\Eloquent\Builder;
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

    public function orders(): Builder
    {
        return $this->concert->orders();
    }

    public function withRecipients(Closure $callback)
    {
        $this->orders()->chunk(20, function(Collection $orders) use($callback) {
            $callback($orders->pluck('email'));
        });
    }

}
