<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AmoLeadController;

Route::post('/create/{name}/{price}', [AmoLeadController::class, 'createLead']);
Route::patch('/update/{id}', [AmoLeadController::class, 'updateLeadDates']);
