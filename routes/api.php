<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AmoLeadController;

Route::post('/create/{name}/{price}', [AmoLeadController::class, 'createLead']);
Route::patch('/update/{id}', [AmoLeadController::class, 'updateLeadDates']);
Route::get('/field/{lead-id}/{field-id}', [AmoLeadController::class, 'getCustomFieldValue']);
Route::patch('/sum/{lead_id}/{field_a_id}/{field_b_id}/{result_field_id}', [AmoLeadController::class, 'updateSumField']);