<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScoreController;

Route::get('/scores', [ScoreController::class, 'index']);

Route::post('/scores', [ScoreController::class, 'store']);

Route::put('/scores', [ScoreController::class, 'update']);

Route::delete('/scores', [ScoreController::class, 'destroy']);

Route::get('/image', [ScoreController::class, 'getImage']);