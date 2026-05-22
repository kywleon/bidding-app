<?php

namespace App\Http\Controllers;

use App\Events\AuctionStatusChanged;
use App\Events\BidPlaced;
use App\Http\Requests\PlaceBidRequest;
use App\Models\Auction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Log;
class AuctionController extends Controller
{
    /**
     * GET /api/auctions/{auction}
     * Returns full auction state (used on page load).
     */
    public function show(Auction $auction): JsonResponse
    {
        Log::info('Showing auction', ['auction_id' => $auction->id]);
        $auction->load(['product', 'bids', 'winnerBid']);

        // Auto-end if time is up and still "active"
        if ($auction->isActive() && $auction->remainingSeconds() === 0) {
            $this->endAuction($auction);
        }

        return response()->json($this->formatAuction($auction));
    }

    /**
     * POST /api/auctions/{auction}/bids
     * Places a new bid on the auction.
     */
    public function placeBid(PlaceBidRequest $request, Auction $auction): JsonResponse
    {
        if ($auction->isEnded()) {
            return response()->json(['message' => 'Auction has already ended.'], 422);
        }

        // Auto-end check: if timer ran out, end instead of accepting bid
        if ($auction->isActive() && $auction->remainingSeconds() === 0) {
            $this->endAuction($auction);
            return response()->json(['message' => 'Auction has already ended.'], 422);
        }

        $amountInCents = $request->integer('amount');
        $currentPrice  = $auction->currentPrice();

        if ($amountInCents <= $currentPrice) {
            return response()->json([
                'message' => 'Bid must be higher than the current price of ' . number_format($currentPrice / 100, 2),
            ], 422);
        }

        $bid = DB::transaction(function () use ($auction, $request, $amountInCents) {
            $bid = $auction->bids()->create([
                'bidder_name' => $request->string('bidder_name'),
                'amount'      => $amountInCents,
            ]);

            // Start the auction on first bid
            if ($auction->isPending()) {
                $auction->start();
                broadcast(new AuctionStatusChanged($auction->fresh()))->toOthers();
            }

            return $bid;
        });

        $auction->refresh()->load(['product', 'bids', 'winnerBid']);
        broadcast(new BidPlaced($auction, $bid));

        return response()->json([
            'bid'     => $this->formatBid($bid),
            'auction' => $this->formatAuction($auction),
        ], 201);
    }

    /**
     * POST /api/auctions/{auction}/end  (called by a scheduler or from frontend poll)
     * Ends the auction if conditions are met.
     */
    public function end(Auction $auction): JsonResponse
    {
        if (! $auction->isActive()) {
            return response()->json(['message' => 'Auction is not active.'], 422);
        }

        if ($auction->remainingSeconds() > 0) {
            return response()->json(['message' => 'Auction countdown is still running.'], 422);
        }

        $this->endAuction($auction);

        return response()->json($this->formatAuction($auction->fresh()->load(['product', 'bids', 'winnerBid'])));
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function endAuction(Auction $auction): void
    {
        $auction->end();
        $auction->refresh()->load(['product', 'winnerBid']);
        broadcast(new AuctionStatusChanged($auction));
    }

    private function formatAuction(Auction $auction): array
    {
        $winner = null;
        if ($auction->isEnded() && $auction->winnerBid) {
            $winner = $this->formatBid($auction->winnerBid);
        }

        return [
            'id'                => $auction->id,
            'status'            => $auction->status,
            'remaining_seconds' => $auction->remainingSeconds(),
            'ends_at'           => $auction->ends_at?->toISOString(),
            'current_price'     => $auction->currentPrice(),
            'winner'            => $winner,
            'product'           => [
                'id'             => $auction->product->id,
                'name'           => $auction->product->name,
                'description'    => $auction->product->description,
                'image_url'      => $auction->product->image_url,
                'starting_price' => $auction->product->starting_price,
            ],
            'bids' => $auction->bids->map(fn ($b) => $this->formatBid($b))->values(),
        ];
    }

    private function formatBid(object $bid): array
    {
        return [
            'id'          => $bid->id,
            'bidder_name' => $bid->bidder_name,
            'amount'      => $bid->amount,
            'created_at'  => $bid->created_at->toISOString(),
        ];
    }
}
