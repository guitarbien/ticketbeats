<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    /**
     * relation with concert
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function concerts()
    {
        return $this->hasMany(Concert::class);
    }
}
