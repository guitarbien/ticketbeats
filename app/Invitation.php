<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Invitation
 * @package App
 */
class Invitation extends Model
{
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
}
