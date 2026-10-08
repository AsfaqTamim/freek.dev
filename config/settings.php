<?php

use App\Models\User;

return [

    'stripe' => [
        'model' => User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
];
