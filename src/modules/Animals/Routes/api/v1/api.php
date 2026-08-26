<?php

use Illuminate\Support\Facades\Route;
use Modules\Animals\Controllers\AnimalController;
use Modules\Animals\Controllers\ManageAnimalController;
use Modules\Animals\Controllers\OrganisationController;
use Modules\Animals\Controllers\TrophyCostController;

Route::get('/animals', [AnimalController::class, 'getAnimals']);
Route::post('/animals/check-availability', [AnimalController::class, 'checkAvailability']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/animals/manage', [ManageAnimalController::class, 'index']);
    Route::post('/animals/manage', [ManageAnimalController::class, 'attach']);
    Route::put('/animals/manage/{animal}/hunters-count', [ManageAnimalController::class, 'updateHuntersCount']);
    Route::delete('/animals/manage/{animal}', [ManageAnimalController::class, 'detach']);

    Route::get('/animals/organisation', [OrganisationController::class, 'index']);
    Route::post('/animals/{animal}/periods', [OrganisationController::class, 'createPeriod']);
    Route::put('/animals/periods/{period}', [OrganisationController::class, 'updatePeriod']);
    Route::delete('/animals/periods/{period}', [OrganisationController::class, 'deletePeriod']);

    Route::get('/animals/trophy-cost', [TrophyCostController::class, 'index']);
    Route::post('/animals/trophy-cost/trophies', [TrophyCostController::class, 'updateTrophy']);
    Route::post('/animals/trophy-cost/fines', [TrophyCostController::class, 'updateFine']);
    Route::post('/animals/trophy-cost/preparations', [TrophyCostController::class, 'updatePreparation']);
});
