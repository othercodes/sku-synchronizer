<?php

namespace App\Commands;

use App\Contracts\LocalProductRepository;
use App\Contracts\RemoteProductRepository;
use App\Models\Product;
use Exception;
use LaravelZero\Framework\Commands\Command;

class ExtractProducts extends Command
{
    protected $signature = 'products:extract';

    protected $description = 'Extract the published products from WooCommerce instance.';

    public function handle(RemoteProductRepository $remote, LocalProductRepository $local): void
    {
        $this->info('Extracting products from WooCommerce instance...');
        try {
            $remote
                ->all()
                ->each(function (Product $product) use ($local) {
                    if (! $product->hasSKU() && $product->isPublished()) {
                        $local->save($product);
                    }
                });
            $this->info('Done!');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
