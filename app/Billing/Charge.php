<?php

namespace App\Billing;

/**
 * Class Charge
 * @package App\Billing
 */
class Charge
{
    /**
     * Charge constructor.
     * @param array $data
     */
    public function __construct(private array $data)
    {
    }

    public function amount()
    {
        return $this->data['amount'];
    }

    public function cardLastFour()
    {
        return $this->data['card_last_four'];
    }

    /**
     * @return string
     */
    public function destination(): string
    {
        return $this->data['destination'];
    }
}
