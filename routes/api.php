<?php

use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;

Route::post('jobs', [JobController::class, 'store']);
Route::get('jobs/{id}', [JobController::class, 'show'])
    ->whereUuid('id');
Route::delete('jobs/{id}', [JobController::class, 'destroy'])
    ->whereUuid('id');
