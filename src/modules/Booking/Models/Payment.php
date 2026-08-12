<?php

namespace Modules\Booking\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends BaseModel
{
    public const string STATUS_PROCESSING = 'processing';
    public const string STATUS_PAID = 'paid';
    public const string STATUS_FAILED = 'failed';
    public const string STATUS_EXPIRED = 'expired';

    protected $table = 'bc_booking_payments';

    protected $fillable = [
        'code',
        'object_id',
        'object_model',
        'booking_id',
        'user_id',
        'payment_gateway',
        'invoice_id',
        'status',
        'amount',
        'currency',
        'payment_url',
        'expires_at',
        'last_checked_at',
        'next_check_at',
        'attempts',
        'logs',
        'create_user',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $payment->code ??= (string) Str::uuid();
        });
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'next_check_at' => 'datetime',
        'attempts' => 'integer',
        'logs' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->processing()
            ->whereNotNull('invoice_id')
            ->where(fn (Builder $query) => $query
                ->whereNull('next_check_at')
                ->orWhere('next_check_at', '<=', now()));
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('create_user', $userId);
    }

    public function isReusable(): bool
    {
        return $this->status === self::STATUS_PROCESSING
            && $this->payment_url !== null
            && $this->expires_at?->isFuture();
    }

    public function transitionToPaid(): bool
    {
        $changed = static::query()
            ->whereKey($this->getKey())
            ->where('status', self::STATUS_PROCESSING)
            ->update([
                'status' => self::STATUS_PAID,
                'last_checked_at' => now(),
                'next_check_at' => null,
                'updated_at' => now(),
            ]);

        if ($changed === 1) {
            $this->refresh();
        }

        return $changed === 1;
    }
}
