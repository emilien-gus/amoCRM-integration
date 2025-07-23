<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AmoLeadController;

Route::patch('/update/{id}', [AmoLeadController::class, 'updateLeadDates']);