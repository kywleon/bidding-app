<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'bidder_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }
}
