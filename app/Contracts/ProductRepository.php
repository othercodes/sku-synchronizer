<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface ProductRepository
{
    public function find(int $id): ?Product;

    /**
     * Return the list of products that match the given filters.
     *
     * @param  array<string, string>  $filters
     * @return Collection<int, Product>|LazyCollection<int, Product>
     */
    public function match(array $filters = []): Collection|LazyCollection;

    /**
     * Returns the complete list of published products.
     *
     * @return Collection<int, Product>|LazyCollection<int, Product>
     */
    public function all(): Collection|LazyCollection;

    /**
     * Persists (creating or updating) the given product or products.
     *
     * @param  Product|Collection<int, Product>|LazyCollection<int, Product>  $product
     * @return Product|Collection<int, Product>|LazyCollection<int, Product>
     */
    public function save(Product|Collection|LazyCollection $product): Product|Collection|LazyCollection;
}
