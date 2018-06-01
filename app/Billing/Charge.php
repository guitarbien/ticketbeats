<?php

namespace App\Billing;

/**
 * Class Charge
 * @package App\Billing
 */
class Charge
{
    /** @var array */
    private $data;

    /**
     * Charge constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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
