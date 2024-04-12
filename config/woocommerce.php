<?php

return [
    'host' => env('WOOCOMMERCE_HOST'),
    'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),
    'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'),
    'wordpress_integration' => env('WOOCOMMERCE_WORDPRESS_INTEGRATION', true),
    'version' => env('WOOCOMMERCE_API_VERSION', 'wc/v3'),
];
