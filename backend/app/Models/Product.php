<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'starting_price',
    ];

    protected $casts = [
        'starting_price' => 'integer',
    ];

    /**
     * Starting price in dollars (human-readable).
     */
    public function getStartingPriceInDollarsAttribute(): float
    {
        return $this->starting_price / 100;
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function activeAuction(): ?Auction
    {
        return $this->auctions()->whereIn('status', ['pending', 'active'])->latest()->first();
    }
}
