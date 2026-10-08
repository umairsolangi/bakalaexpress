<?php

return [
    // Whether push notifications are enabled (default false)
    'enabled' => env('PUSH_ENABLED', false),

    // Expo Push endpoint URL
    'expo_url' => env('EXPO_PUSH_URL', 'https://exp.host/--/api/v2/push/send'),

    // Optional Expo Access Token for enhanced rate limits
    'access_token' => env('EXPO_ACCESS_TOKEN', null),

    // Request timeout in seconds
    'timeout' => 5,

    // Android notification channel identifier
    'android_channel_id' => 'orders',

    // Maximum device tokens stored per account
    'max_tokens_per_account' => 5,

    // Window in seconds for suppressing duplicate push sends
    'dedupe_seconds' => 10,

    // Maximum number of online approved riders notified per order ready event
    'rider_broadcast_limit' => 200,

    // Whether chat push notifications include text preview
    'chat_preview' => true,
];
