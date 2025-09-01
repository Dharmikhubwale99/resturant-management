<?php

return [
    'instance_id' => env('WHATSAPP_INSTANCE_ID'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'group_id' => env('WHATSAPP_GROUP_ID'),
    'base_url' => env('WHATSAPP_BASE_URL' ?? 'https://sender.hubwale.in/api/send'),
];
