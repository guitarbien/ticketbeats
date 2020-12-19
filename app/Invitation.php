<?php

namespace App;

use App\Mail\InvitationEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

/**
 * Class Invitation
 * @package App
 */
class Invitation extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @param string $code
     * @return mixed
     */
    public static function findByCode(string $code): Invitation
    {
        return self::where('code', $code)->firstOrFail();
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool
     */
    public function hasBeenUsed(): bool
    {
        return $this->user_id !== null;
    }

    public function send()
    {
        Mail::to($this->email)->send(new InvitationEmail($this));
    }
}
