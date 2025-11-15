<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/events', [EventController::class, 'index'])->name('api.events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('api.events.show');
Route::get('/events/{event}/availability', [EventController::class, 'availability'])->name('api.events.availability'); 