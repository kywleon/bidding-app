<?php

use App\Http\Controllers\AuctionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auctions')->group(function () {
    Route::get('{auction}', [AuctionController::class, 'show']);
    Route::post('{auction}/bids', [AuctionController::class, 'placeBid']);
    Route::post('{auction}/end', [AuctionController::class, 'end']);
});
