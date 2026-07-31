<?php

declare(strict_types=1);

return [
    'name' => 'College CMS',
    'env' => 'local',
    'debug' => true,
    'url' => '', // e.g. http://localhost/college-cms/admin — leave blank to auto-detect
    'public_url' => '', // e.g. http://localhost/college-cms/public — leave blank to auto-detect
    'timezone' => 'Asia/Kolkata',
    'session' => [
        'name' => 'college_cms_session',
        'lifetime' => 7200, // seconds
    ],
    'remember' => [
        'cookie' => 'college_cms_remember',
        'days' => 30,
    ],
    'csrf_token_key' => '_csrf_token',
];
