<?php

declare(strict_types=1);

return [
    'project_id' => getenv('FIREBASE_PROJECT_ID') ?: 'shiftscheduler-37b31',
    'service_account_path' => getenv('FIREBASE_SERVICE_ACCOUNT_PATH') ?: __DIR__ . '/firebase-service-account.json',
    'vapid_public_key' => getenv('FIREBASE_VAPID_PUBLIC_KEY') ?: 'BGJBu88tNGWcIXcaNggbhASsKOz_i_3u-49ZbBKNBlUkGgBpIQbcvpitcfBao4xUAV2CpPaB24F3XN1p7M-Z-5I',
];
