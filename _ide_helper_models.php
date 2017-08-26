<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
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
 */
	class Concert extends \Eloquent {}
}

namespace App{
/**
 * App\Order
 *
 * @property int $id
 * @property string $confirmation_number
 * @property int $amount
 * @property string $email
 * @property string $card_last_four
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Concert $concert
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ticket[] $tickets
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereConfirmationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereUpdatedAt($value)
 */
	class Order extends \Eloquent {}
}

namespace App{
/**
 * App\Ticket
 *
 * @property int $id
 * @property int $concert_id
 * @property int|null $order_id
 * @property string|null $reserved_at
 * @property string|null $code
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Concert $concert
 * @property-read mixed $price
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket available()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket|\Illuminate\Database\Query\Builder sold()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereConcertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereReservedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket whereUpdatedAt($value)
 */
	class Ticket extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Concert[] $concerts
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

