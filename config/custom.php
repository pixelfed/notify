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
];
