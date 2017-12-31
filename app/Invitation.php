<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public static function findByCode(string $code)
    {
        return self::where('code', $code)->first();
    }

    public function hasBeenUsed()
    {
        return $this->user_id !== null;
    }
}
