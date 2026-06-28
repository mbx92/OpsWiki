<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'plan_id',
        'number',
        'status',
        'currency',
        'subtotal_cents',
        'tax_cents',
        'total_cents',
        'billing_interval',
        'period_start',
        'period_end',
        'due_at',
        'paid_at',
        'notes',
        'line_items',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'line_items' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidCents(): int
    {
        return (int) $this->payments()
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount_cents');
    }

    public function balanceCents(): int
    {
        return max(0, $this->total_cents - $this->paidCents());
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || $this->balanceCents() === 0;
    }

    public function formattedTotal(): string
    {
        return self::formatCents($this->total_cents, $this->currency);
    }

    public static function formatCents(int $cents, string $currency = 'USD'): string
    {
        $symbol = match ($currency) {
            'IDR' => 'Rp ',
            'EUR' => '€',
            default => '$',
        };

        $decimals = $currency === 'IDR' ? 0 : 2;

        return $symbol.number_format($cents / 100, $decimals);
    }
}
