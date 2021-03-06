<?php

namespace App;

use App\Facades\TicketCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class Ticket
 * @package App
 */
class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @param Builder $query
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    /**
     * @param Builder $query
     */
    public function scopeSold($query)
    {
        return $query->whereNotNull('order_id');
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function release()
    {
        $this->update(['reserved_at' => null]);
    }

    public function claimFor(Order $order)
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
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
