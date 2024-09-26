<?php

return [
    'allowed_domains' => env('APP_ALLOWED_DOMAINS', 'pixelfed.social'),

    'apns' => [
        'key_id' => env('PN_APNS_KEY_ID'),
        'team_id' => env('PN_APNS_TEAM_ID'),
        'app_bundle_id' => env('PN_APNS_APP_BUNDLE_ID', 'com.pixelfed'),
        'private_key_path' => env('PN_APNS_PRIVATE_KEY_PATH'),
        'private_key_secret' => env('PN_APNS_PRIVATE_KEY_SECRET'),
    ],

    'expo_token' => env('PN_EXPO_TOKEN', false),

    // max is 100, but we'll halve that for now
    'expo_batch_size' => env('PN_EXPO_BATCH_SIZE', 2),
    // max is 6, but we'll halve that for now
    'expo_max_req_batch' => env('PN_EXPO_MAX_REQ_BATCH', 3),
    // set delivery ttl, default is 2 weeks
    'expo_ttl' => env('PN_EXPO_TTL', 1209600),
    // set delivery priority
    'expo_priority' => env('PN_EXPO_PRIORITY', 'high'),
];
