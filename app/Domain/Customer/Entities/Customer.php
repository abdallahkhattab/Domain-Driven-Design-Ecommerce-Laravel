<?php

namespace App\Domain\Customer\Entities;

use App\Domain\Customer\ValueObjects\CustomerId;

class Customer
{
    private CustomerId $id;
    private string $name;
    private string $email;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        CustomerId $id,
        string $name,
        string $email
    ) {
        $this->id = $id;
        $this->setName($name);
        $this->setEmail($email);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function changeName(string $name): void
    {
        $this->setName($name);
    }

    public function changeEmail(string $email): void
    {
        $this->setEmail($email);
    }

    private function setName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Customer name cannot be empty');
        }
        
        if (strlen($name) > 255) {
            throw new \InvalidArgumentException('Customer name cannot exceed 255 characters');
        }

        $this->name = trim($name);
    }

    private function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        $this->email = strtolower($email);
    }

    // Getters
    public function getId(): CustomerId { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}