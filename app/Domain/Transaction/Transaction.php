<?php

namespace App\Domain\Transaction;

use App\Domain\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'amount',
        'date',
    ];

    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }

    public function getAmount(): ?float
    {
        return isset($this->attributes['amount']) ? (float) $this->attributes['amount'] : null;
    }

    public function setAmount(float $amount): self
    {
        $this->attributes['amount'] = $amount;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        if (!isset($this->attributes['date'])) {
            return null;
        }
        $val = $this->attributes['date'];
        if ($val instanceof \DateTimeInterface) {
            return $val;
        }
        return new \DateTimeImmutable($val);
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->attributes['date'] = $date;
        return $this;
    }

    public function customerRelation(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getCustomer(): ?Customer
    {
        if (isset($this->relations['customerRelation'])) {
            return $this->relations['customerRelation'];
        }
        return $this->customerRelation;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->attributes['customer_id'] = $customer?->getId();
        $this->setRelation('customerRelation', $customer);
        return $this;
    }
}