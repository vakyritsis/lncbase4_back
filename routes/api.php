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
Route::get('/LncBaseV4/initialtissues', [InitialCallController::class, 'get_tissues']);
Route::get('/LncBaseV4/initialmethods', [InitialCallController::class, 'get_methods']);
Route::get('/LncBaseV4/initialexpressions', [InitialCallController::class, 'get_expressions']);
Route::get('/LncBaseV4/initiallocalizations', [InitialCallController::class, 'get_localizations']);
Route::get('/LncBaseV4/initialinteractions', [InitialCallController::class, 'get_interactions']);
Route::get('/LncBaseV4/testRoute', function () {
    return 'Hello World';
});
