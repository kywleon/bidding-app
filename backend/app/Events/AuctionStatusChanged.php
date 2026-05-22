<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Auction $auction,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("auction.{$this->auction->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auction.status';
    }

    public function broadcastWith(): array
    {
        $winner = null;
        if ($this->auction->isEnded() && $this->auction->winnerBid) {
            $winner = [
                'bidder_name' => $this->auction->winnerBid->bidder_name,
                'amount'      => $this->auction->winnerBid->amount,
            ];
        }

        return [
            'auction_id'        => $this->auction->id,
            'status'            => $this->auction->status,
            'remaining_seconds' => $this->auction->remainingSeconds(),
            'ends_at'           => $this->auction->ends_at?->toISOString(),
            'current_price'     => $this->auction->currentPrice(),
            'winner'            => $winner,
        ];
    }
}
