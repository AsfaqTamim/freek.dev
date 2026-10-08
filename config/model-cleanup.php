<?php

use App\Services\Models\WebhookCall;

return [

    /*
     * All models in this array that implement `Spatie\ModelCleanup\GetsCleanedUp`
     * will be cleaned.
     */
    'models' => [
        WebhookCall::class,
    ],
];
