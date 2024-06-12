<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\InitialCallController;
use App\Http\Controllers\ExpressionsController;
use App\Http\Controllers\LocalizationsController;


Route::get('/LncBaseV4/interactions/keyword', [InteractionController::class, 'show']);
Route::get('/LncBaseV4/interactions/keyword', [InteractionController::class, 'show']);
Route::get('/LncBaseV4/expressions', [ExpressionsController::class, 'show']);
Route::get('/LncBaseV4/localizations', [LocalizationsController::class, 'show']);
Route::get('/LncBaseV4/initial', [InitialCallController::class, 'getAll']);
