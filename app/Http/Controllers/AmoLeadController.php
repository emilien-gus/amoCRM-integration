<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AmoLeadService;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessWebhookjob;

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

    public function updateSumField(Request $request, int $leadId, int $fieldAId, int $fieldBId, int $resultFieldId){
        $amoLeadService = app(AmoLeadService::class);
        $fieldValue = $amoLeadService->sumFields($leadId, $fieldAId, $fieldBId, $resultFieldId);
    }

    public function webhookHandler(Request $request)
    {
        $raw = file_get_contents('php://input');
        parse_str($raw, $parsed);

        $leadId = $parsed['leads']['update'][0]['id'] ?? null;
        $updateDateFieldId = $request->input('update_date_field_id');
        $updateTextFieldId = $request->input('update_text_field_id');

        // Валидация
        if (!$leadId || !$updateDateFieldId || !$updateTextFieldId) {
            Log::warning('Webhook: отсутствуют обязательные параметры');
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Запускаем job асинхронно
        ProcessWebhookjob::dispatch($leadId, $updateDateFieldId, $updateTextFieldId);

        return response()->json(['status' => 'accepted'], 200);
    }
}
