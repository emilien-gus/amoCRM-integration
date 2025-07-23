<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AmoLeadService;

class AmoLeadController extends Controller
{
    public function updateLeadDates(Request $request, int $id)
    {
        $amoLeadService = app(AmoLeadService::class);
        $amoLeadService->updateLeadDates($id);
    }
}
