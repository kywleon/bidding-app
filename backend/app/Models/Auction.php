<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    use HasFactory;

    const DURATION_SECONDS = 60;

    protected $fillable = [
        'product_id',
        'status',
        'started_at',
        'ends_at',
        'winner_bid_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at'    => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->orderByDesc('amount');
    }

    public function winnerBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'winner_bid_id');
    }

    /**
     * Highest bid so far.
     */
    public function highestBid(): ?Bid
    {
        return $this->bids()->orderByDesc('amount')->first();
    }

    /**
     * Current price: highest bid amount, or product starting price.
     */
    public function currentPrice(): int
    {
        return $this->highestBid()?->amount ?? $this->product->starting_price;
    }

    /**
     * Remaining seconds. Returns 0 when expired or not started.
     */
    public function remainingSeconds(): int
    {
        if ($this->status !== 'active' || ! $this->ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->ends_at, false));
    }

    /**
     * Start the auction countdown.
     */
    public function start(): void
    {
        $this->update([
            'status'     => 'active',
            'started_at' => now(),
            'ends_at'    => now()->addSeconds(self::DURATION_SECONDS),
        ]);
    }

    /**
     * End the auction and assign winner.
     */
    public function end(): void
    {
        $winner = $this->highestBid();
        $this->update([
            'status'        => 'ended',
            'winner_bid_id' => $winner?->id,
        ]);
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isActive(): bool  { return $this->status === 'active'; }
    public function isEnded(): bool   { return $this->status === 'ended'; }
}
