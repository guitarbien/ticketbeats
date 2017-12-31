<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Invitation
 * @package App
 */
class Invitation extends Model
{
    /**
     * @param string $code
     * @return mixed
     */
    public static function findByCode(string $code): Invitation
    {
        return self::where('code', $code)->firstOrFail();
    }

    /**
     * @return bool
     */
    public function hasBeenUsed(): bool
    {
        return $this->user_id !== null;
    }
}
