<?php

use Illuminate\Support\Facades\Route;

// Make calculator the homepage
Route::get('/', [\App\Http\Controllers\CalculatorController::class, 'index']);
Route::get('/calculator', [\App\Http\Controllers\CalculatorController::class, 'index']);
Route::post('/calculator', [\App\Http\Controllers\CalculatorController::class, 'calculate']);
Route::post('/api/calc', [\App\Http\Controllers\CalculatorController::class, 'calculate']);

