<?php

return [
    'default' => strtoupper((string) env('APP_CURRENCY', 'USD')),

    'supported' => [
        'USD',
        'CDF',
    ],

    'symbols' => [
        'USD' => '$',
        'CDF' => 'FC',
    ],
];
