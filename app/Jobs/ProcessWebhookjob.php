<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessWebhookjob implements ShouldQueue
{
    use Queueable;

    private int $leadId;
    private int $updateDateFieldId;
    private int $updateTextFieldId;

    public function __construct(int $leadId, int $updateDateFieldId, int $updateTextFieldId)
    {
        $this->leadId = $leadId;
        $this->updateDateFieldId = $updateDateFieldId;
        $this->updateTextFieldId = $updateTextFieldId;
    }

    public function handle(AmoLeadService $amoLeadService): void
    {
        $amoLeadService->updateFromWebhook(
            $this->leadId,
            $this->updateDateFieldId,
            $this->updateTextFieldId
        );
    }
}
