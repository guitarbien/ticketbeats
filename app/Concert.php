<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Class Concert
 * @package App
 */
class Concert extends Model
{
    protected $guarded = [];
    protected $dates   = ['date'];

    /**
     * relation with user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * relation with attendeeMessages
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendeeMessages()
    {
        return $this->hasMany(AttendeeMessage::class);
    }

    /**
     * @param Builder $query
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published_at !== null;
    }

    public function publish()
    {
        $this->update(['published_at' => $this->freshTimestamp()]);
        $this->addTickets($this->ticket_quantity);
    }

    /**
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    /**
     * @return string
     */
    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    /**
     * @return string
     */
    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    /**
     * @return Order
     */
    public function orders()
    {
        return Order::whereIn('id', $this->tickets()->pluck('order_id'));
    }

    /**
     * @param $customerEmail
     * @return bool
     */
    public function hasOrderFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    /**
     * @param $customerEmail
     * @return Order[]|\Illuminate\Database\Eloquent\Collection
     */
    public function ordersFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Ticket
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @param $quantity
     * @param $email
     * @return Reservation
     */
    public function reserveTickets($quantity, $email)
    {
        $tickets = $this->findTickets($quantity)->each(function(Ticket $ticket) {
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    /**
     * @param $quantity
     * @return Ticket[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Builder[]|\Illuminate\Support\Collection
     */
    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity)
        {
            throw new NotEnoughTicketsException;
        }

        return $tickets;
    }

    /**
     * @param $quantity
     * @return $this
     */
    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i)
        {
            $this->tickets()->create([]);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    /**
     * @return int
     */
    public function ticketsSold()
    {
        return $this->tickets()->sold()->count();
    }

    /**
     * @return int
     */
    public function totalTickets()
    {
        return $this->tickets()->count();
    }

    /**
     * @return string
     */
    public function percentSoldOut()
    {
        return number_format(($this->ticketsSold() / $this->totalTickets()) * 100, 2);
    }

    /**
     * @return float|int
     */
    public function revenueInDollars()
    {
        return $this->orders()->sum('amount') / 100;
    }

    /**
     * @return bool
     */
    public function hasPoster()
    {
        return $this->poster_image_path !== null;
    }

    /**
     * @return string
     */
    public function posterUrl()
    {
        return Storage::disk('public')->url($this->poster_image_path);
    }
}
