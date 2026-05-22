<?php

namespace Tests\Unit;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionModelTest extends TestCase
{
    use RefreshDatabase;

    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        $product = Product::create([
            'name'           => 'Unit Test Product',
            'starting_price' => 500000,
        ]);

        $this->auction = Auction::create([
            'product_id' => $product->id,
            'status'     => 'pending',
        ]);
    }

    public function test_is_pending_when_created(): void
    {
        $this->assertTrue($this->auction->isPending());
        $this->assertFalse($this->auction->isActive());
        $this->assertFalse($this->auction->isEnded());
    }

    public function test_start_sets_correct_state(): void
    {
        $this->auction->start();

        $this->assertTrue($this->auction->isActive());
        $this->assertNotNull($this->auction->started_at);
        $this->assertNotNull($this->auction->ends_at);
        $this->assertEqualsWithDelta(
            Auction::DURATION_SECONDS,
            $this->auction->remainingSeconds(),
            2
        );
    }

    public function test_end_sets_winner(): void
    {
        $this->auction->start();

        $bid = Bid::create([
            'auction_id'  => $this->auction->id,
            'bidder_name' => 'Winner',
            'amount'      => 600000,
        ]);

        $this->auction->end();

        $this->assertTrue($this->auction->isEnded());
        $this->assertEquals($bid->id, $this->auction->winner_bid_id);
    }

    public function test_remaining_seconds_is_zero_when_pending(): void
    {
        $this->assertEquals(0, $this->auction->remainingSeconds());
    }

    public function test_remaining_seconds_is_zero_when_ended(): void
    {
        $this->auction->update(['status' => 'ended']);
        $this->assertEquals(0, $this->auction->remainingSeconds());
    }

    public function test_highest_bid_returns_null_when_no_bids(): void
    {
        $this->assertNull($this->auction->highestBid());
    }

    public function test_highest_bid_returns_correct_bid(): void
    {
        Bid::create(['auction_id' => $this->auction->id, 'bidder_name' => 'A', 'amount' => 510000]);
        Bid::create(['auction_id' => $this->auction->id, 'bidder_name' => 'B', 'amount' => 550000]);
        Bid::create(['auction_id' => $this->auction->id, 'bidder_name' => 'C', 'amount' => 530000]);

        $highest = $this->auction->highestBid();
        $this->assertEquals(550000, $highest->amount);
        $this->assertEquals('B', $highest->bidder_name);
    }
}
