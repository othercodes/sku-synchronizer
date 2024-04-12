<?php

namespace App\Commands;

use App\Contracts\LocalProductRepository;
use App\Exceptions\ProductNotFound;
use App\Services\ProductSKUUpdater;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use LaravelZero\Framework\Commands\Command;
use Psr\Log\LoggerInterface;

class SetProductSKU extends Command
{
    protected $signature = 'product:sku {product?} {sku?} {--f|from-file=} {--m|match-name}';

    protected $description = 'Sets the product sku. The --from-file options runs a bulk set from a csv file.';

    public function handle(LocalProductRepository $repository, LoggerInterface $logger): void
    {
        $updater = new ProductSKUUpdater($repository, $logger);

        foreach ($this->getInputProducts() as $input) {
            try {
                $product = $updater->update($input['product'], $input['sku'], $this->option('match-name'));
                $this->info(strtr('Updating product {name} ({id}) with new SKU {sku}.', [
                    '{id}' => $product->id,
                    '{name}' => $product->name,
                    '{sku}' => $product->sku,
                ]));
            } catch (ProductNotFound $e) {
                $this->error($e->getMessage());
            }
        }
    }

    /**
     * Provides the input products to change the sku.
     *
     * @return Enumerable<int, array<string, string>>
     */
    private function getInputProducts(): Enumerable
    {
        if ($this->option('from-file') !== null) {
            return (new LazyCollection(function () {
                /** @var resource $handle */
                $handle = fopen((string) $this->option('from-file'), 'r');
                while (($line = fgetcsv($handle)) !== false) {
                    yield $line;
                }
            }))->map(function (array $columns) {
                return array_combine(['product', 'sku'], $columns);
            });
        }

        return new Collection([
            [
                'product' => is_numeric($this->argument('product'))
                    ? (int) $this->argument('product')
                    : (string) $this->argument('product'),
                'sku' => (string) $this->argument('sku'),
            ],
        ]);
    }
}
