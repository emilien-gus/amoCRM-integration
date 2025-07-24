<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AmoLeadService;

class AmoLeadController extends Controller
{
    public function createLead(Request $request, string $name, int $price)
    {
        $amoLeadService = app(AmoLeadService::class);
        $amoLeadService->createNewLead($name, $price);
    }
    public function updateLeadDates(Request $request, int $id)
    {
        $amoLeadService = app(AmoLeadService::class);
        $amoLeadService->updateLeadDates($id);
    }
    public function getCustomFieldValue(Request $request, int $leadId, int $fieldId){
        $amoLeadService = app(AmoLeadService::class);
        $fieldValue = $amoLeadService->getCustomFieldValueById($leadId, $fieldId);

        return response()->json(['field_value' => $fieldValue]);
    }
}
