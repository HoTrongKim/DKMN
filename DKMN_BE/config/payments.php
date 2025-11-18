<?php

use Illuminate\Support\Str;

return [
    'test_amount_vnd' => env('TEST_AMOUNT_VND', 1200),
    'default_fare_vnd' => env('DEFAULT_FARE_VND', 1200),
    'allowed_amount_delta' => (int) env('ALLOWED_AMOUNT_DELTA', 0),
    'default_provider' => env('PAYMENT_QR_PROVIDER', 'vietqr'),
    'display_min_vnd' => (int) env('TRIP_PRICE_MIN_VND', 0),
    'display_max_vnd' => (int) env('TRIP_PRICE_MAX_VND', 0),
    'ticket_hold_minutes' => (int) env('TICKET_HOLD_MINUTES', 10),
    'providers' => [
        'vietqr' => [
            'label' => 'VietQR',
            // Timo (Ban Viet) - cập nhật tài khoản QR mặc định
            'account' => env('VIETQR_ACCOUNT', 'BVB-0793587033'),
        ],
        'momo' => [
            'label' => 'MoMo',
            // Dùng QR VietQR với tài khoản Timo (Ban Viet)
            'account' => env('VIETQR_ACCOUNT', 'BVB-0793587033'),
        ],
        'zalopay' => [
            'label' => 'ZaloPay',
            'account' => env('ZALOPAY_ACCOUNT', 'ZP-1234567890'),
        ],
    ],
    'timo' => [
        'api_url' => env('TIMO_API_URL', 'https://api-timo.dzmid.io.vn'),
        'username' => env('TIMO_USERNAME'),
        'password' => env('TIMO_PASSWORD'),
        'account' => env('TIMO_ACCOUNT', '0793587033'),
    ],
    'intent_expiration_minutes' => env('PAYMENT_INTENT_EXP_MIN', 15),
    'webhook_secret' => env('WEBHOOK_SECRET', Str::random(32)),
];
