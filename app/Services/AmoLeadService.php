<?php

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use Illuminate\Support\Facades\Log;

class AmoLeadService{
    protected AmoCRMApiClient $client;

    public function __construct(AmoCRMApiClient $client)
    {
        $this->client = $client;
    }

    public function UpdateLeadDates(int $id){
        $leadId = null;
        try {
            $amoClient = app(AmoCRMApiClient::class);
            
            $lead = $amoClient->leads()->getOne($id);
            $leadId = $lead->getId();
            
            // Устанавливаем кастомные поля
            $lead->setCustomFieldsValues([
                [
                    'field_id' => 1135061, // ID поля типа "Дата"
                    'values' => [['value' => now()->format('Y-m-d')]]
                ],
                [
                    'field_id' => 1135009, // ID поля типа "Текст"
                    'values' => [['value' => now()->toIso8601String()]]
                ],
                [
                    'field_id' => 1135059, // ID поля типа "Число"
                    'values' => [['value' => now()->timestamp]]
                ]
            ]);
        
            $amoClient->leads()->updateOne($lead);
        
            Log::info('Lead updated', ['lead_id' => $leadId]);
        
        } catch (\Exception $e) {
            Log::error('Failed to update lead', [
                'lead_id' => $leadId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}