<?php

declare(strict_types=1);

namespace App\Model;

use Money\Money;

class Transaction
{
    private const TX_TYPE_DEPOSIT = 'deposit';
    private const TX_TYPE_WITHDRAW = 'withdraw';
    private const CLIENT_TYPE_PRIVATE = 'private';
    private const CLIENT_TYPE_BUSINESS = 'business';

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

    public function isClientPrivate(): bool
    {
        return $this->clientType === self::CLIENT_TYPE_PRIVATE;
    }

    public function isClientBusiness(): bool
    {
        return $this->clientType === self::CLIENT_TYPE_BUSINESS;
    }

    public function setClientType($clientType): self
    {
        $this->clientType = $clientType;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isDeposit(): bool
    {
        return $this->type === self::TX_TYPE_DEPOSIT;
    }

    public function isWithdraw(): bool
    {
        return $this->type === self::TX_TYPE_WITHDRAW;
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

    public function setFee(?Money $fee): self
    {
        $this->fee = $fee;
        return $this;
    }
}
