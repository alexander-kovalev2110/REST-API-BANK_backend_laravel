<?php

namespace App\Application\Transaction\DTO;

final class FilterTransactionRequest
{
    public function __construct(
        public readonly ?\DateTimeImmutable $date = null,
        public readonly ?float $amount = null,
        public readonly int $page = 1,
        public readonly int $limit = 10,
    ) {}
}