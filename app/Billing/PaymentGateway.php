<?php

namespace App\Billing;

/**
 * Interface PaymentGateway
 * @package App\Billing
 */
interface PaymentGateway
{
    /**
     * @param $amount
     * @param $token
     * @param string $destinationAccountId
     * @return mixed
     */
    public function charge($amount, $token, string $destinationAccountId);

    /**
     * @return string
     */
    public function getValidTestToken(): string;

    public function newChargesDuring($callback);
}
