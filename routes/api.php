<?php

use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('ticket/buy', [TicketController::class, 'buy']);

Route::post('midtrans/callback', [CallbackController::class, 'callback']);
