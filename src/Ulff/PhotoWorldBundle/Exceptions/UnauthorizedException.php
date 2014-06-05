<?php

namespace Ulff\PhotoWorldBundle\Exceptions;

class UnauthorizedException extends \Exception
{
    protected $message = 'You have to be logged in to perform this action';

    public function __construct($customMessage = null)
    {
        if(!empty($customMessage)) {
            $this->message = $customMessage;
        }
    }
}
