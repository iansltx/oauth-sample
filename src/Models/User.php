<?php

namespace App\Models;

class User
{
    protected string $firstName;
    protected string $lastName;
    protected int $id;

    public function __construct(int $id, string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->id = $id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHash(): string
    {
        return hash('sha256', $this->id . '-super-secret');
    }
}
