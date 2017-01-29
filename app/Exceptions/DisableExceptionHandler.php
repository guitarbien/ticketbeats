<?php

namespace App\Exceptions;

use \Exception;

class DisableExceptionHandler extends Handler {
    public function __construct() {}

    public function report(Exception $e) {}

    public function render($request, Exception $e)
    {
        throw $e;
    }
}
