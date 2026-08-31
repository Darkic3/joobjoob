<?php

namespace App\Payments;

class PaymentVerifyDTO
{
    protected string $status;
    protected array $verify_data;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getVerifyData(): array
    {
        return $this->verify_data;
    }

    public static function fromData(string $status, array $verifyData = []): self
    {
        $self = new self();
        $self->status = $status;
        $self->verify_data = $verifyData;

        return $self;
    }
}
