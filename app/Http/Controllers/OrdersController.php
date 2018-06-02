<?php

namespace App\Http\Controllers;

use App\Order;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends Controller
{
    /**
     * @param string $confirmationNumber
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string $confirmationNumber)
    {
        $order = Order::findByConfirmationNumber($confirmationNumber);
        return view('orders.show', ['order' => $order]);
    }
}
