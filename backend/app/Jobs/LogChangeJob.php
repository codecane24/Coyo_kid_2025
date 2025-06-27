<?php

namespace App\Jobs;

use App\Models\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogChangeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $modelType;
    public $modelId;
    public $action;
    public $changes;
    public $userId;

    public function __construct($modelType, $modelId, $action, $changes, $userId)
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->action = $action;
        $this->changes = $changes;
        $this->userId = $userId;
    }

    public function handle()
    {
        dd($this->modelType, $this->modelId, $this->action, $this->changes, $this->userId);
        Log::create([
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
            'action' => $this->action,
            'changes' => $this->changes,
            'user_id' => $this->userId,
        ]);
    }
}
