<?php

namespace App\Observers;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as Logger; // For debugging

class ModelObserver
{
    /**
     * Handle the "created" event.
     */
    public function created(Model $model)
    {
        $this->logChange($model, 'create', null, $model->getAttributes());
    }

    /**
     * Handle the "updated" event.
     */
    public function updated(Model $model)
    {
        $original = $model->getOriginal();
        $changes = $model->getChanges();

        // Exclude unchanged fields (optional optimization)
        $relevantChanges = array_intersect_key($changes, $original);

        $this->logChange($model, 'update', $original, $relevantChanges);
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(Model $model)
    {
        $this->logChange($model, 'delete', $model->getAttributes(), null);
    }
    protected function logChange(Model $model, string $action, $oldData, $newData)
    {
        try {
            // Skip logging for UserActivityLog to avoid infinite loop
            if ($model instanceof \App\Models\UserActivityLog) {
                return;
            }

            // Debug: Log inputs before processing
            \Log::info('ModelObserver Debug Before Processing', [
                'model_type' => get_class($model),
                'action' => $action,
                'oldData_raw' => $oldData,
                'newData_raw' => $newData,
            ]);

            // Ensure $oldData and $newData are arrays, even if they are null
            $oldData = is_array($oldData) ? $oldData : (is_null($oldData) ? [] : (array) $oldData);
            $newData = is_array($newData) ? $newData : (is_null($newData) ? [] : (array) $newData);

            // Exclude sensitive fields like passwords (customizable)
            $excludedFields = ['password', 'remember_token'];
            $oldData = $this->filterExcludedFields($oldData, $excludedFields);
            $newData = $this->filterExcludedFields($newData, $excludedFields);

            // Save payload for logging
            $payload = request()->all(); // Capture the request payload

            // JSON encode the payload to ensure it's a string
            $payloadJson = $this->safeJsonEncode($payload);

            // Prepare `old_data` and `new_data` as JSON
            $oldDataJson = $this->safeJsonEncode($oldData);
            $newDataJson = $this->safeJsonEncode($newData);

            // Log the data before saving it
            \Log::info('ModelObserver Final Prepared Data', [
                'old_data' => $oldDataJson,
                'new_data' => $newDataJson,
                'payload' => $payloadJson,
            ]);

            // Check the prepared data for insertion
            \Log::info('Data Prepared for Insert', [
                'old_data_json' => $oldDataJson,
                'new_data_json' => $newDataJson,
            ]);

            // Use withoutEvents to avoid recursive observer triggering
            \App\Models\UserActivityLog::withoutEvents(function () use ($oldDataJson, $newDataJson, $payloadJson) {
                // Insert the data into the database
                $log = \App\Models\UserActivityLog::create([
                    'user_id' => auth()->id() ?? null, // Handle guests
                    'url' => request()->url(), // Capturing actual URL for the request
                    'method' => request()->method(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'payload' => $payloadJson, // Now storing the payload as a JSON string
                    'old_data' => $oldDataJson ?: '[]', // Ensure old_data is valid
                    'new_data' => $newDataJson ?: '[]', // Ensure new_data is valid
                ]);

                // Log if the data was saved successfully
                \Log::info('UserActivityLog created successfully', [
                    'log_id' => $log->id,
                    'old_data' => $log->old_data,
                    'new_data' => $log->new_data,
                ]);
            });

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Logging error in ModelObserver: ' . $e->getMessage(), [
                'model' => get_class($model),
                'action' => $action,
                'oldData' => $oldData ?? null,
                'newData' => $newData ?? null,
            ]);
        }
    }


    // Helper function to safely encode data to JSON
    protected function safeJsonEncode($data)
    {
        $json = json_encode($data);

        // Check if json_encode succeeded
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log the error for debugging
            \Log::error('JSON Encoding Error: ' . json_last_error_msg(), ['data' => $data]);
            return '[]'; // Return an empty array as a fallback
        }

        return $json;
    }





    /**
     * Filter excluded fields from the data array.
     */
    protected function filterExcludedFields($data, array $excludedFields)
    {
        // Return an empty array if $data is not an array
        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($key) use ($excludedFields) {
            return !in_array($key, $excludedFields);
        }, ARRAY_FILTER_USE_KEY);
    }

}
