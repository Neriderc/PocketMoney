<?php

namespace App\Exception;

use Exception;

class InvalidRoleException extends Exception
{
    public function __construct($validRoles)
    {
        $roles = implode(', ', $validRoles);
        $message = "Invalid role provided. Valid roles are: $roles";
        parent::__construct($message);
    }
}