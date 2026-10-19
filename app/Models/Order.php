<?php

namespace App\Models;

use App\Services\InventoryService;
use App\Services\OrderNotificationService;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const ALLOWED_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => ['cancelled'],
        'cancelled' => [],
    ];

    protected $fillable = [
        'order_number',
        'customer_id',
        'discount_id',
        'coupon_code',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'inventory_restored_at',
        'subtotal',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'total',
        'shipping_address',
        'billing_address',
        'customer_email',
        'customer_phone',
        'customer_first_name',
        'customer_last_name',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'paid_at' => 'datetime',
        'inventory_restored_at' => 'datetime',
    ];

    public function getPaymentMethodAttribute($value): ?string
    {
        return $value ?? ($this->billing_address['payment']['method'] ?? 'cod');
    }

    public function getPaymentReferenceAttribute($value): ?string
    {
        return $value ?? ($this->billing_address['payment']['reference'] ?? null);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $method = $this->payment_method;

        return match ($method) {
            'cod' => 'Cash on Delivery',
            'card' => 'Credit / Debit Card',
            'upi' => 'UPI',
            default => ucfirst((string) $method),
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->latest('id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    /**
     * Check whether transitioning from current status to target status is permitted.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        if ($this->status === $targetStatus) {
            return false;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        return in_array($targetStatus, $allowed, true);
    }

    /**
     * Execute a validated order status transition with audit logging and lifecycle inventory handling.
     */
    public function transitionTo(string $targetStatus, ?string $reason = null, ?User $user = null): self
    {
        if ($this->status === $targetStatus) {
            return $this;
        }

        if (! $this->canTransitionTo($targetStatus)) {
            throw new DomainException("Cannot transition order from [{$this->status}] to [{$targetStatus}].");
        }

        $fromStatus = $this->status;

        DB::transaction(function () use ($fromStatus, $targetStatus, $reason, $user) {
            $inventoryService = app(InventoryService::class);

            // Lifecycle inventory rules
            if (in_array($targetStatus, ['shipped', 'delivered'], true) && in_array($fromStatus, ['pending', 'processing'], true)) {
                $inventoryService->releaseReservation($this, $user?->id);
            }

            if (in_array($targetStatus, ['cancelled', 'refunded'], true)) {
                $inventoryService->restoreStock($this, $reason ?: $targetStatus, $user?->id);
            }

            $this->update([
                'status' => $targetStatus,
            ]);

            OrderStatusHistory::create([
                'order_id' => $this->id,
                'from_status' => $fromStatus,
                'to_status' => $targetStatus,
                'user_id' => $user?->id ?? auth()->id(),
                'user_name' => $user?->name ?? (auth()->user()?->name ?? 'System / Webhook'),
                'reason' => $reason ?: "Status updated to {$targetStatus}",
            ]);

            // Dispatch transactional notifications based on new status
            if ($targetStatus === 'shipped') {
                app(OrderNotificationService::class)->notifyOrderShipped($this);
            } elseif ($targetStatus === 'delivered') {
                app(OrderNotificationService::class)->notifyOrderDelivered($this);
            } elseif ($targetStatus === 'cancelled') {
                app(OrderNotificationService::class)->notifyOrderCancelled($this, $reason);
            }
        });

        return $this;
    }

    public function markAsPaid(string $transactionId, ?string $gateway = null, ?array $payload = null): self
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_reference' => $transactionId,
            'paid_at' => now(),
            'payment_method' => $gateway ?? $this->payment_method,
        ]);

        if ($this->canTransitionTo('processing')) {
            $this->transitionTo('processing', 'Payment received and verified', auth()->user());
        }

        app(OrderNotificationService::class)->notifyPaymentConfirmed($this);

        return $this;
    }

    public function markAsFailed(?string $reason = null, ?array $payload = null): self
    {
        $this->update([
            'payment_status' => 'failed',
        ]);

        OrderStatusHistory::create([
            'order_id' => $this->id,
            'from_status' => $this->status,
            'to_status' => $this->status,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System / Gateway',
            'reason' => $reason ?: 'Payment failed at gateway',
            'metadata' => ['event' => 'payment_failed'],
        ]);

        return $this;
    }

    public function markAsRefunded(?string $refundReference = null, ?float $amount = null, ?array $payload = null): self
    {
        $this->update([
            'payment_status' => 'refunded',
        ]);

        if ($this->canTransitionTo('cancelled')) {
            $this->transitionTo('cancelled', 'Payment refunded and order cancelled', auth()->user());
        } else {
            app(InventoryService::class)->restoreStock($this, 'refunded', auth()->id());
        }

        app(OrderNotificationService::class)->notifyOrderRefunded($this, $amount);

        return $this;
    }
}
