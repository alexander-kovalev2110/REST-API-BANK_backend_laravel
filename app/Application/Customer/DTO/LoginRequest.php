<?php

namespace App\Application\Customer\DTO;

class LoginRequest
{
    public ?string $name = null;
    public ?string $password = null;
}