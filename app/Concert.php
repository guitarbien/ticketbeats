<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * App\Concert
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $subtitle
 * @property \Carbon\Carbon $date
 * @property int $ticket_price
 * @property string $venue
 * @property string $venue_address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string|null $additional_information
 * @property string|null $published_at
 * @property int $ticket_quantity
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $formatted_date
 * @property-read mixed $formatted_start_time
 * @property-read mixed $ticket_price_in_dollars
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ticket[] $tickets
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert published()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereTicketPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereTicketQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereVenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereVenueAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert whereZip($value)
 * @mixin \Eloquent
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

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function orders()
    {
        return Order::whereIn('id', $this->tickets()->pluck('order_id'));
    }

    public function hasOrderFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

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

    public function reserveTickets($quantity, $email)
    {
        $tickets = $this->findTickets($quantity)->each(function(Ticket $ticket) {
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity)
        {
            throw new NotEnoughTicketsException;
        }

        return $tickets;
    }

    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i)
        {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function ticketsSold()
    {
        return $this->tickets()->sold()->count();
    }

    public function totalTickets()
    {
        return $this->tickets()->count();
    }

    public function percentSoldOut()
    {
        return number_format(($this->ticketsSold() / $this->totalTickets()) * 100, 2);
    }

    public function revenueInDollars()
    {
        return $this->orders()->sum('amount') / 100;
    }

    public function hasPoster()
    {
        return $this->post_image_path !== null;
    }

    public function posterUrl()
    {
        return Storage::disk('public')->url($this->poster_image_path);
    }
}
