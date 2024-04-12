<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepository;
use App\Exceptions\ProductNotFound;
use App\Models\Product;
use Psr\Log\LoggerInterface;

final readonly class ProductSKUUpdater
{
    private ProductFinder $finder;

    public function __construct(
        private ProductRepository $repository,
        private LoggerInterface $logger
    ) {
        $this->finder = new ProductFinder($this->repository, $this->logger);
    }

    /**
     * Updates the given product by id with the given sku.
     *
     * If $guessByName is true, provided product is a string and the initial
     * search is not successful try an experimental match by name similarity.
     * The default precision is 95 percent. If more than one match the highest
     * will be used to perform the operation.
     *
     * @throws ProductNotFound
     */
    public function update(int|string $product, string $sku, bool $guessByName = false): Product
    {
        try {
            $product = $this->finder->byId($product);
        } catch (ProductNotFound $e) {
            if (! (is_string($product) && $guessByName)) {
                throw $e;
            }

            $guess = $this->finder->search($product)->first();
            if ($guess == null) {
                throw $e;
            }

            $product = $guess['product'];
        }

        // set the new product sku.
        $product->sku = trim($sku);

        $this->repository->save($product);

        return $product;
    }
}
