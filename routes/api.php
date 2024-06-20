<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\InitialCallController;
use App\Http\Controllers\ExpressionsController;
use App\Http\Controllers\LocalizationsController;


Route::get('/LncBaseV4/interactions/keyword', [InteractionController::class, 'showByKeyword']);
Route::get('/LncBaseV4/interactions/location', [InteractionController::class, 'showByLocation']);
Route::get('/LncBaseV4/expressions', [ExpressionsController::class, 'show']);
Route::get('/LncBaseV4/localizations', [LocalizationsController::class, 'show']);
Route::get('/LncBaseV4/initial', [InitialCallController::class, 'getAll']);
Route::get('/LncBaseV4/testRoute', function () {
    return 'Hello World';
});
