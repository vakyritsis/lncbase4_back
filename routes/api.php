<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\InitialCallController;
use App\Http\Controllers\ExpressionsController;


Route::get('/LncBaseV4/interactions', [InteractionController::class, 'show']);
Route::get('/LncBaseV4/expressions', [ExpressionsController::class, 'show']);
Route::get('/LncBaseV4/initialCall', [InitialCallController::class, 'getAll']);
