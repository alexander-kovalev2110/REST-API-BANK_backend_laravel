<?php

namespace App\Domain\Customer;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    protected $table = 'customers';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'pw',
    ];

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getName(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setName(string $name): self
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->attributes['pw'] ?? '';
    }

    public function setPassword(string $password): self
    {
        $this->attributes['pw'] = $password;
        return $this;
    }
}