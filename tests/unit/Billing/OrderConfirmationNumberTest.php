<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderConfirmationNumberTest extends TestCase
{
    // Must be unique
    // Can only contain uppercase letters and numbers
    // Cannot contain ambiguous characters (1, I, 0, O)
    // Must be 16 charaters long
    //
    // ABCDEFGHJKLMNPQRSTUVWXYZ
    // 23456789
}
