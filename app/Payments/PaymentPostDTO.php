<?php

namespace App\Payments;

class PaymentPostDTO
{
    protected string $status;
    protected array $post_data;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPostData(): array
    {
        return $this->post_data;
    }

    public static function fromData(string $status, array $postData = []): self
    {
        $self = new self();
        $self->status = $status;
        $self->post_data = $postData;

        return $self;
    }
}
