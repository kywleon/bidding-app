<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Auction $auction,
        public readonly Bid $bid,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("auction.{$this->auction->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'bid.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id'       => $this->auction->id,
            'status'           => $this->auction->status,
            'remaining_seconds'=> $this->auction->remainingSeconds(),
            'ends_at'          => $this->auction->ends_at?->toISOString(),
            'current_price'    => $this->auction->currentPrice(),
            'bid' => [
                'id'           => $this->bid->id,
                'bidder_name'  => $this->bid->bidder_name,
                'amount'       => $this->bid->amount,
                'created_at'   => $this->bid->created_at->toISOString(),
            ],
        ];
    }
}
