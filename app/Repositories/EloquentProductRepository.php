<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\LocalProductRepository;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class EloquentProductRepository implements LocalProductRepository
{
    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function match(array $filters = []): Collection|LazyCollection
    {
        $query = Product::query();

        foreach ($filters as $field => $value) {
            $query->where($field, '=', $value);
        }

        return $query->get();
    }

    public function all(): Collection|LazyCollection
    {
        return Product::all();
    }

    public function save(Product|Collection|LazyCollection $product): Product|Collection|LazyCollection
    {
        return $product instanceof Product
            ? $this->saveOne($product)
            : $this->saveMany($product);
    }

    private function saveOne(Product $product): Product
    {
        $product->save();
        return $product;
    }

    /**
     * Persists many products at once.
     *
     * @param  Collection<int, Product>|LazyCollection<int, Product>  $products
     * @return Collection<int, Product>|LazyCollection<int, Product>
     */
    private function saveMany(Collection|LazyCollection $products): Collection|LazyCollection
    {
        $products->each(function (Product $product) {
            $product->save();
        });

        return $products;
    }
}
