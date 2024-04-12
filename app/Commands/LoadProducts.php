<?php

namespace App\Commands;

use App\Contracts\LocalProductRepository;
use App\Contracts\RemoteProductRepository;
use Exception;
use LaravelZero\Framework\Commands\Command;

class LoadProducts extends Command
{
    protected $signature = 'product:load';

    protected $description = 'Load the published products into WooCommerce instance.';

    public function handle(RemoteProductRepository $remote, LocalProductRepository $local): void
    {
        $this->info('Loading products into WooCommerce instance...');

        try {
            $pushed = $remote->save($local->all());

            $this->info("Loaded {$pushed->count()} products!");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
