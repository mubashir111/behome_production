<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";
    protected $fillable = [
        'order_serial_no',
        'user_id',
        'tax',
        'discount',
        'subtotal',
        'total',
        'shipping_charge',
        'order_type',
        'order_datetime',
        'payment_method',
        'payment_status',
        'status',
        'reason',
        'source',
        'admin_viewed_at',
    ];

    protected $casts = [
        'id'              => 'integer',
        'order_serial_no' => 'string',
        'user_id'         => 'integer',
        'tax'             => 'decimal:6',
        'discount'        => 'decimal:6',
        'subtotal'        => 'decimal:6',
        'total'           => 'decimal:6',
        'shipping_charge' => 'decimal:6',
        'order_type'      => 'integer',
        'order_datetime'  => 'datetime',
        'payment_method'  => 'integer',
        'payment_status'  => 'integer',
        'status'          => 'integer',
        'reason'           => 'string',
        'source'           => 'integer',
        'admin_viewed_at'  => 'datetime',
    ];

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function orderProducts(): \Illuminate\Database\Eloquent\Relations\morphMany
    {
        return $this->morphMany(Stock::class, 'model');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function outletAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderOutletAddress::class);
    }

    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_method');
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', OrderStatus::CONFIRMED);
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', OrderStatus::ON_THE_WAY);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', OrderStatus::DELIVERED);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', OrderStatus::CANCELED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', OrderStatus::REJECTED);
    }

    public function returnAndRefund(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReturnAndRefund::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    public function audits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderAudit::class)->latest();
    }

    public function reasonPayload(): array
    {
        if (blank($this->reason)) {
            return [];
        }

        $payload = json_decode($this->reason, true);
        return is_array($payload) ? $payload : [];
    }

    public function customerNote(): ?string
    {
        $payload = $this->reasonPayload();
        if (isset($payload['customer_note'])) {
            return $payload['customer_note'] ?: null;
        }

        if (!blank($this->reason) && !in_array($this->status, [OrderStatus::CANCELED, OrderStatus::REJECTED], true)) {
            return $this->reason;
        }

        return null;
    }

    public function adminStatusReason(): ?string
    {
        $payload = $this->reasonPayload();
        if (isset($payload['status_reason'])) {
            return $payload['status_reason'] ?: null;
        }

        if (!blank($this->reason) && in_array($this->status, [OrderStatus::CANCELED, OrderStatus::REJECTED], true)) {
            return $this->reason;
        }

        return null;
    }

    public function setCustomerNote(?string $note): void
    {
        $payload = $this->reasonPayload();

        if (!blank($note)) {
            $payload['customer_note'] = $note;
        } else {
            unset($payload['customer_note']);
        }

        $this->reason = blank($payload) ? null : json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public function setAdminStatusReason(?string $note): void
    {
        $payload = $this->reasonPayload();

        if (!blank($note)) {
            $payload['status_reason'] = $note;
        } else {
            unset($payload['status_reason']);
        }

        $this->reason = blank($payload) ? null : json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
