<?php

namespace App;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AttendeeMessage
 * @package App
 */
class AttendeeMessage extends Model
{
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    /**
     * @return Builder
     */
    public function orders(): Builder
    {
        return $this->concert->orders();
    }

    /**
     * @param int $chunkSize
     * @param Closure $callback
     */
    public function withChunkedRecipients(int $chunkSize, Closure $callback)
    {
        $this->orders()->chunk($chunkSize, function(Collection $orders) use($callback) {
            $callback($orders->pluck('email'));
        });
    }

}
