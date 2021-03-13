<?php
declare(strict_types=1);

namespace App\Model;

use Money\Money;

class Transaction
{
    private \DateTimeImmutable $date;
    private int $clientId;
    private string $clientType;
    private string $type;
    private Money $value;
    private ?Money $fee = null;

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function setClientType($clientType): self
    {
        $this->clientType = $clientType;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setValue(Money $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): Money
    {
        return $this->value;
    }

    public function getFee(): ?Money
    {
        return $this->fee;
    }

    public function setFee(Money $fee): self
    {
        $this->fee = $fee;
        return $this;
    }
}
