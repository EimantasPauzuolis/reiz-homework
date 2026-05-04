<?php

return [
    'selenium_host' => env('SELENIUM_HOST', 'http://selenium:4444'),
    'request_timeout' => env('SCRAPER_TIMEOUT', 30),
    'wait_for_selector' => env('SCRAPER_WAIT_SELECTOR', 'body'),
];
