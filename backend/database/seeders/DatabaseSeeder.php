<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Auction;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $product = Product::create([
            'name'           => 'Vintage Rolex Submariner 1965',
            'description'    => 'An exceptionally rare timepiece in near-mint condition. Original dial, original bracelet. Comes with box and papers.',
            'image_url'      => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600',
            'starting_price' => 1900000, // $19,000.00 in cents
        ]);

        Auction::create([
            'product_id' => $product->id,
            'status'     => 'pending',
        ]);

        $this->command->info("Seeded: Product ID {$product->id}, Auction ID 1");
        $this->command->info("Visit: GET /api/auctions/1");
    }
}
