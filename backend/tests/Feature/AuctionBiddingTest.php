<?php

namespace Tests\Feature;

use App\Events\AuctionStatusChanged;
use App\Events\BidPlaced;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuctionBiddingTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name'           => 'Test Watch',
            'starting_price' => 1000000, // $10,000
        ]);

        $this->auction = Auction::create([
            'product_id' => $this->product->id,
            'status'     => 'pending',
        ]);
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function test_can_fetch_auction_details(): void
    {
        $response = $this->getJson("/api/auctions/{$this->auction->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id', 'status', 'remaining_seconds', 'current_price', 'product', 'bids',
            ])
            ->assertJson([
                'id'            => $this->auction->id,
                'status'        => 'pending',
                'current_price' => 1000000,
            ]);
    }

    // ── Place bid ────────────────────────────────────────────────────────────

    public function test_first_bid_starts_auction_and_broadcasts(): void
    {
        Event::fake();

        $response = $this->postJson("/api/auctions/{$this->auction->id}/bids", [
            'bidder_name' => 'Alice',
            'amount'      => 1010000, // $10,100
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('auction.status', 'active')
            ->assertJsonPath('bid.bidder_name', 'Alice');

        $this->auction->refresh();
        $this->assertEquals('active', $this->auction->status);
        $this->assertNotNull($this->auction->started_at);

        Event::assertDispatched(AuctionStatusChanged::class);
        Event::assertDispatched(BidPlaced::class);
    }

    public function test_bid_must_be_higher_than_current_price(): void
    {
        // Place first valid bid to set current price
        $this->postJson("/api/auctions/{$this->auction->id}/bids", [
            'bidder_name' => 'Alice',
            'amount'      => 1010000,
        ]);

        // Try to bid lower
        $response = $this->postJson("/api/auctions/{$this->auction->id}/bids", [
            'bidder_name' => 'Bob',
            'amount'      => 1005000, // lower than Alice's bid
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Bid must be higher than the current price of 10,100.00']);
    }

    public function test_bid_amount_must_be_positive(): void
    {
        $response = $this->postJson("/api/auctions/{$this->auction->id}/bids", [
            'bidder_name' => 'Alice',
            'amount'      => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_bid_requires_name(): void
    {
        $response = $this->postJson("/api/auctions/{$this->auction->id}/bids", [
            'amount' => 1100000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bidder_name']);
    }

    public function test_cannot_bid_on_ended_auction(): void
    {
        $this->auction->update(['status' => 'ended']);

        $response = $this->postJson("/api/auctions/{$this->auction->id}/bids", [
            'bidder_name' => 'Alice',
            'amount'      => 1100000,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Auction has already ended.']);
    }

    // ── End auction ──────────────────────────────────────────────────────────

    public function test_can_end_expired_auction(): void
    {
        Event::fake();

        // Start auction with an already-expired timer
        $this->auction->update([
            'status'     => 'active',
            'started_at' => now()->subMinutes(2),
            'ends_at'    => now()->subMinutes(1),
        ]);

        Bid::create([
            'auction_id'  => $this->auction->id,
            'bidder_name' => 'Alice',
            'amount'      => 1100000,
        ]);

        $response = $this->postJson("/api/auctions/{$this->auction->id}/end");

        $response->assertOk()
            ->assertJsonPath('status', 'ended')
            ->assertJsonPath('winner.bidder_name', 'Alice');

        Event::assertDispatched(AuctionStatusChanged::class);
    }

    public function test_cannot_end_auction_with_remaining_time(): void
    {
        $this->auction->update([
            'status'  => 'active',
            'ends_at' => now()->addMinute(),
        ]);

        $response = $this->postJson("/api/auctions/{$this->auction->id}/end");

        $response->assertStatus(422);
    }

    // ── Current price ────────────────────────────────────────────────────────

    public function test_current_price_equals_starting_price_before_any_bids(): void
    {
        $this->assertEquals($this->product->starting_price, $this->auction->currentPrice());
    }

    public function test_current_price_equals_highest_bid(): void
    {
        Bid::create(['auction_id' => $this->auction->id, 'bidder_name' => 'A', 'amount' => 1100000]);
        Bid::create(['auction_id' => $this->auction->id, 'bidder_name' => 'B', 'amount' => 1200000]);

        $this->auction->refresh();
        $this->assertEquals(1200000, $this->auction->currentPrice());
    }
}
