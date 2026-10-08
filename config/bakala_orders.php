<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bakala Express Customer Orders Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for order placement, abuse protection, and defaults.
    |
    */

    // Default delivery fee applied to orders (web hardcodes 0.00)
    'delivery_fee' => 0.00,

    // Maximum number of active orders a customer may have simultaneously
    'max_active_orders' => 3,

    // Active order statuses considered towards abuse limit and active list
    'active_statuses' => [
        'pending',
        'confirmed_by_seller',
        'preparing',
        'ready_for_pickup',
        'assigned_to_rider',
        'picked_up',
    ],

    // Terminal order statuses for history
    'history_statuses' => [
        'delivered',
        'completed',
        'cancelled',
        'rejected',
    ],

    // Estimated delivery time offset in minutes
    'estimated_delivery_minutes' => 45,

    // Idempotency cache duration in hours
    'idempotency_ttl_hours' => 24,

    // Maximum active orders a rider can hold simultaneously (web enforces no limit, API defaults to 2)
    'rider_max_active_orders' => 2,

    // Whether delivery proof image is required upon delivery (default false)
    'require_delivery_proof' => false,

    // Low stock threshold for seller catalog management (default 5)
    'catalog_low_stock_threshold' => 5,

    // Maximum price multiplier over base price for seller custom price (default 3.0)
    'catalog_price_max_multiplier' => 3.0,

    // Admin email address for account deletion requests
    'admin_email' => env('ADMIN_EMAIL', 'admin@bakalaexpress.com'),

    // Hours after order close during which order chat remains open
    'chat_open_hours_after_close' => (int) env('CHAT_OPEN_HOURS_AFTER_CLOSE', 48),
];
