<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->string('bidder_name');
            $table->unsignedBigInteger('amount'); // stored in cents
            $table->timestamps();

            $table->index(['auction_id', 'amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
