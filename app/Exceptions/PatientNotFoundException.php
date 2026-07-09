<?php

namespace App\Exceptions;

use Exception;

class PatientNotFoundException extends Exception
{
    public function __construct(string $message = 'Patient not found.', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
