<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly_cents',
        'price_yearly_cents',
        'currency',
        'features',
        'limits',
        'gates',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
            'gates' => 'array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function formattedMonthlyPrice(): string
    {
        if ($this->price_monthly_cents === 0) {
            return 'Free';
        }

        return $this->formatCents($this->price_monthly_cents);
    }

    public function formattedYearlyPrice(): string
    {
        if ($this->price_yearly_cents === 0) {
            return 'Free';
        }

        return $this->formatCents($this->price_yearly_cents);
    }

    private function formatCents(int $cents): string
    {
        $currency = strtoupper((string) $this->currency);

        $symbol = match ($currency) {
            'IDR' => 'Rp ',
            'EUR' => '€',
            default => '$',
        };

        $amount = number_format($cents / 100, $currency === 'IDR' ? 0 : 2);

        return $symbol.$amount;
    }
}
