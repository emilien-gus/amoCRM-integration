<?php

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\DateCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\SelectCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\DateCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\SelectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\DateCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\SelectCustomFieldValueModel;
use Carbon\Carbon;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use Illuminate\Support\Facades\Log;

class AmoLeadService{
    protected AmoCRMApiClient $client;

    public function __construct(AmoCRMApiClient $client)
    {
        $this->client = $client;
    }

    public function createNewLead(string $name, int $price){
        //создаём новую сделку 
        $lead = new LeadModel();
        $lead->setName($name)
            ->setPrice($price)
            ->setContacts(
                (new ContactsCollection())
            ->add(
                (new ContactModel())
                    ->setId(44783029)
                )
            )
            ->setCompany(
                (new CompanyModel())
                    ->setId(44783027)
            );
            
            //добавим кастомные поля
            $leadCustomFieldsValues = new CustomFieldsValuesCollection();
            
            // --- добавим поле текста (fieldId: 1135009) ---
            $сustomFieldValueModel = new TextCustomFieldValuesModel();
            $сustomFieldValueModel->setFieldId(1135009);
            $leadCustomFieldsValues->add($сustomFieldValueModel);

            // --- добавим поле дата (fieldId: 1135061) ---
            $сustomFieldValueModel = new DateCustomFieldValuesModel();
            $сustomFieldValueModel->setFieldId(1135061);
            $leadCustomFieldsValues->add($сustomFieldValueModel);
        
            // --- добавим поле число  (fieldId: 1135059) ---
            $сustomFieldValueModel = new NumericCustomFieldValuesModel();
            $сustomFieldValueModel->setFieldId(1135059);
            $leadCustomFieldsValues->add($сustomFieldValueModel);

            // --- Добавим поле SELECT (одиночный выбор) (fieldId: 1135063) ---
            $selectField = new SelectCustomFieldValuesModel();
            $selectField->setFieldId(1135063);
            $selectField->setValues(
                (new SelectCustomFieldValueCollection())
                    ->add(
                        (new SelectCustomFieldValueModel())
                            ->setValue('Вариант 1') 
                    )
            );

            // применяем поля к сделке
            $lead->setCustomFieldsValues($leadCustomFieldsValues);

            //постим новую сделку
            $amoClient = app(AmoCRMApiClient::class);
            $amoClient->leads()->addOne($lead);
    }
    
    public function UpdateLeadDates(int $id)
    {
        $amoClient = app(AmoCRMApiClient::class);
        try {
            $lead = $amoClient->leads()->getOne($id);
            } catch (AmoCRMApiNoContentException $e) {
                Log::warning("Сделка с ID $id не найдена");
                return null; // или throw, или response()->json(['error' => ...])
            } catch (AmoCRMApiException $e) {
                Log::error("Ошибка AmoCRM API: " . $e->getMessage());
                return null;
        }
        // Получаем коллекцию или создаём новую
        $customFields = $lead->getCustomFieldsValues() ?? new CustomFieldsValuesCollection();

        // --- Обновим поле даты (fieldId: 1135061) ---
        $dateField = $customFields->getBy('fieldId', 1135061);
        if (!$dateField) {
            $dateField = (new DateCustomFieldValuesModel())->setFieldId(1135061);
            $customFields->add($dateField);
        }

        $dateField->setValues(
            (new DateCustomFieldValueCollection())->add(
                (new DateCustomFieldValueModel())->setValue(Carbon::today())
            )
        );

        // --- Обновим поле текста (fieldId: 1135009) ---
        $textField = $customFields->getBy('fieldId', 1135009);
        if (!$textField) {
            $textField = (new TextCustomFieldValuesModel())->setFieldId(1135009);
            $customFields->add($textField);
        }

        $textField->setValues(
            (new TextCustomFieldValueCollection())->add(
                (new TextCustomFieldValueModel())->setValue(now()->toIso8601String())
            )
        );

        // --- Обновим числовое поле (fieldId: 1135059) ---
        $numericField = $customFields->getBy('fieldId', 1135059);
        if (!$numericField) {
            $numericField = (new NumericCustomFieldValuesModel())->setFieldId(1135059);
            $customFields->add($numericField);
        }

        $numericField->setValues(
            (new NumericCustomFieldValueCollection())->add(
                (new NumericCustomFieldValueModel())->setValue(now()->timestamp)
            )
        );

        // Применим обновлённые поля и сохраним сделку
        $lead->setCustomFieldsValues($customFields);
        $amoClient->leads()->updateOne($lead);
    }

    public function getCustomFieldValueById(int $leadId, int $fieldId){
        $amoClient = app(AmoCRMApiClient::class);
        try {
            $lead = $amoClient->leads()->getOne($leadId);
            } catch (AmoCRMApiNoContentException $e) {
                Log::warning("Сделка с ID $leadId не найдена");
                return null;
            } catch (AmoCRMApiException $e) {
                Log::error("Ошибка AmoCRM API: " . $e->getMessage());
                return null;
        }
        $customFields = $lead->getCustomFieldsValues();
        if (!$customFields) {
            return null;
        }

        $field = $customFields->getBy('fieldId', $fieldId);
        if (!$field || !$field->getValues()) {
            return null;
        }

        // Получаем первое значение поля
        $valueModel = $field->getValues()->first();
        return $valueModel->getValue();
    }

    public function sumFields(int $leadId, int $fieldAId, int $fieldBId, int $resultFieldId){
        $amoClient = app(AmoCRMApiClient::class);
        try {
            $lead = $amoClient->leads()->getOne($leadId);
            } catch (AmoCRMApiNoContentException $e) {
                Log::warning("Сделка с ID $leadId не найдена");
                return null; // или throw, или response()->json(['error' => ...])
            } catch (AmoCRMApiException $e) {
                Log::error("Ошибка AmoCRM API: " . $e->getMessage());
                return null;
        }
        
        $customFields = $lead->getCustomFieldsValues();
        if (!$customFields) {
            return null;
        }

        $fieldA = $customFields->getBy('fieldId', $fieldAId);
        $fieldB = $customFields->getBy('fieldId', $fieldBId);
        if(!$fieldA || !$fieldB){
            return null;
        }

        $fieldAValueCollection = $fieldA->getValues();
        $fieldBValueCollection = $fieldB->getValues();

        $isNumericPair = ($fieldAValueCollection instanceof NumericCustomFieldValueCollection) && ($fieldBValueCollection instanceof NumericCustomFieldValueCollection);
        $isTextPair = ($fieldAValueCollection instanceof TextCustomFieldValueCollection) && ($fieldBValueCollection instanceof TextCustomFieldValueCollection);

        $valueA = $fieldAValueCollection->first()->getValue();
        $valueB = $fieldBValueCollection->first()->getValue();
        if (!$valueA || !$valueB){
            return null;
        }

        if ($isNumericPair){
            $result = $valueA + $valueB;
            $resultField = $customFields->getBy('fieldId', $resultFieldId);
            if (!$resultField) {
                $resultField = (new NumericCustomFieldValuesModel())->setFieldId($resultFieldId);
                $customFields->add($resultField);
            }

            $resultField->setValues(
                (new NumericCustomFieldValueCollection())->add(
                    (new NumericCustomFieldValueModel())->setValue($result)
                )
            );
        }else if ($isTextPair){
            $result = $valueA . $valueB;
            $resultField = $customFields->getBy('fieldId', $resultFieldId);
            if (!$resultField) {
                $resultField = (new TextCustomFieldValuesModel())->setFieldId($resultFieldId);
                $customFields->add($resultField);
            }

            $resultField->setValues(
                (new TextCustomFieldValueCollection())->add(
                    (new TextCustomFieldValueModel())->setValue($result)
                )
            );
        }else{
            return null;
        }

        // Применим обновлённые поля и сохраним сделку
        $lead->setCustomFieldsValues($customFields);
        $amoClient->leads()->updateOne($lead);        
    }

    public function updateFromWebhook(int $leadId, int $updateDateFieldId, int $updateTextFieldId)
    {
        $amoClient = app(AmoCRMApiClient::class);
        try {
            $lead = $amoClient->leads()->getOne($leadId);
        } catch (AmoCRMApiNoContentException $e) {
            Log::warning("Сделка с ID $leadId не найдена");
            return null; // или throw, или response()->json(['error' => ...])
        } catch (AmoCRMApiException $e) {
            Log::error("Ошибка AmoCRM API: " . $e->getMessage());
            return null;
        }

        $customFields = $lead->getCustomFieldsValues();
        if (!$customFields) {
            return null;
        }
        $customFields = $lead->getCustomFieldsValues() ?? new CustomFieldsValuesCollection();

        // --- Обновим поле даты  ---
        $dateField = $customFields->getBy('fieldId', $updateDateFieldId);
        if (!$dateField) {
            $dateField = (new DateCustomFieldValuesModel())->setFieldId($updateDateFieldId);
            $customFields->add($dateField);
        }

        $dateField->setValues(
            (new DateCustomFieldValueCollection())->add(
                (new DateCustomFieldValueModel())->setValue(Carbon::today())
            )
        );

        // --- Обновим поле текста  ---
        $textField = $customFields->getBy('fieldId', $updateTextFieldId);
        if (!$textField) {
            $textField = (new TextCustomFieldValuesModel())->setFieldId($updateTextFieldId);
            $customFields->add($textField);
        }

        $textField->setValues(
            (new TextCustomFieldValueCollection())->add(
                (new TextCustomFieldValueModel())->setValue(now()->toIso8601String())
            )
        );

        // Применим обновлённые поля и сохраним сделку
        $lead->setCustomFieldsValues($customFields);
        $amoClient->leads()->updateOne($lead);
    }
}