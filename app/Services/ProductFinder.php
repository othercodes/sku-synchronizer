<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepository;
use App\Exceptions\ProductNotFound;
use App\Models\Product;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final readonly class ProductFinder
{
    public function __construct(
        private ProductRepository $repository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Finds a product by unique id or slug.
     *
     * @throws ProductNotFound
     */
    public function byId(int|string $id): Product
    {
        $product = match (gettype($id)) {
            'integer' => $this->repository->find($id),
            'string' => $this->repository->match(['slug' => $id])->first(),
        };

        if ($product === null) {
            throw new ProductNotFound("Product $id not found.");
        }

        return $product;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function search(string $name, int $precision = 100): Collection
    {
        $products = new Collection();

        $this->logger->info("Searching product $name with precision $precision.");
        foreach ($this->repository->all() as $product) {
            similar_text(
                str_slug($product->name),
                str_slug($name),
                $percent
            );

            if ($percent >= $precision) {
                $this->logger->info("Product $product->name matched with $name with accurate of $percent.");
                $products->push([
                    'product' => $product,
                    'percent' => $percent,
                ]);
            }
        }

        return $products;
    }
}
