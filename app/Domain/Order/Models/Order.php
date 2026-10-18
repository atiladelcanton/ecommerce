<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Support\BrazilianMoney;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'number',
        'checkout_token',
        'checkout_session_hash',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_document',
        'status',
        'payment_status',
        'subtotal_cents',
        'shipping_cents',
        'discount_cents',
        'total_cents',
        'shipping_pricing_status',
        'shipping_option_id',
        'shipping_provider', 'shipping_integration_id', 'shipping_environment',
        'shipping_service_id',
        'shipping_name',
        'shipping_description',
        'shipping_quote_snapshot',
        'carrier',
        'tracking_code',
        'tracking_url',
        'invoice_key',
        'dce_key',
        'shipping_document_type',
        'ready_for_shipping_at',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_pricing_status' => ShippingPricingStatus::class,
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal_cents' => 'integer',
            'shipping_cents' => 'integer',
            'discount_cents' => 'integer',
            'total_cents' => 'integer',
            'shipping_quote_snapshot' => 'array',
            'shipping_document_type' => ShipmentDocumentType::class,
            'ready_for_shipping_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected $hidden = ['checkout_token', 'checkout_session_hash'];

    public function formattedTotal(): string
    {
        return $this->total_cents === null ? 'A definir' : BrazilianMoney::display($this->total_cents);
    }

    public function formattedShipping(): string
    {
        return $this->shipping_cents === null ? 'A definir' : BrazilianMoney::display($this->shipping_cents);
    }

    public function shippingQuotes(): HasMany
    {
        return $this->hasMany(OrderShippingQuote::class)->orderByDesc('revision');
    }

    public function latestShippingQuote(): HasOne
    {
        return $this->hasOne(OrderShippingQuote::class)->ofMany('revision', 'max');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
