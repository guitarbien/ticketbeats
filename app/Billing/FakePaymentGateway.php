<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = '4242424242424242';

    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    /**
     * @param string $cardNumber
     * @return string
     */
    public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string
    {
        $token = 'fake-tok_' . str_random(24);
        $this->tokens[$token] = $cardNumber;
        return $token;
    }

    /**
     * @return int
     */
    public function totalCharges(): int
    {
        return $this->charges->map->amount()->sum();
    }

    /**
     * @param string $accountId
     * @return int
     */
    public function totalChargesFor(string $accountId): int
    {
        return $this->charges->filter(function ($charge) use ($accountId) {
            return $charge->destination() === $accountId;
        })->map->amount()->sum();
    }

    /**
     * @param $amount
     * @param $token
     * @param string $destinationAccountId
     * @return Charge|mixed
     */
    public function charge($amount, $token, string $destinationAccountId)
    {
        if ($this->beforeFirstChargeCallback !== null)
        {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if (!$this->tokens->has($token))
        {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
            'destination' => $destinationAccountId,
        ]);
    }

    public function newChargesDuring($callback)
    {
        $chargesFrom = $this->charges->count();

        $callback($this);

        return $this->charges->slice($chargesFrom)->reverse()->values();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}