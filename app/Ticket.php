<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserve_at');
    }

    public function reserve()
    {
        $this->update(['reserve_at' => Carbon::now()]);
    }

    public function release()
    {
        $this->update(['reserve_at' => null]);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }
}
