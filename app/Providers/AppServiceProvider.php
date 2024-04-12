<?php

namespace App\Providers;

use App\Contracts\LocalProductRepository;
use App\Contracts\RemoteProductRepository;
use App\Repositories\EloquentProductRepository;
use App\Repositories\HTTPWooCommerceProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LocalProductRepository::class, EloquentProductRepository::class);

        $this->app->singleton(RemoteProductRepository::class, fn () => new HTTPWooCommerceProductRepository(
            config('woocommerce.host'),
            config('woocommerce.consumer_key'),
            config('woocommerce.consumer_secret'),
            [
                'wp_api' => config('woocommerce.wordpress_integration'),
                'version' => config('woocommerce.version'),
            ]
        )
        );
    }
}
