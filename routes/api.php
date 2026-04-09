<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OnlineBillingController;

Route::get('/online-billing', [OnlineBillingController::class, 'index']);
Route::get('/online-billing/{id}', [OnlineBillingController::class, 'show']);
