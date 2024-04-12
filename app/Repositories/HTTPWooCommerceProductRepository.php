<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\RemoteProductRepository;
use App\Models\Product;
use Automattic\WooCommerce\Client;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use stdClass;

class HTTPWooCommerceProductRepository extends Client implements RemoteProductRepository
{
    public function find(int $id): ?Product
    {
        try {
            $product = $this->get("products/$id");

            return new Product([
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status,
            ]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     *  IMPORTANT: Currently, the filters are not working
     *  properly in the WooCommerce API, so ALL the products
     *  are returned, publish, draft and so on.
     *
     * @return Collection<int, Product>|LazyCollection<int, Product>
     */
    public function match(array $filters = []): Collection|LazyCollection
    {
        return new LazyCollection(function () use ($filters) {
            $page = 1;
            do {
                /** @var array<int, stdClass> $buffer */
                $buffer = $this->get('products', [
                    'filter' => $filters,
                    'page' => $page++,
                    'per_page' => 25,
                ]);

                foreach ($buffer as $product) {
                    yield new Product([
                        'id' => $product->id,
                        'name' => $product->name,
                        'status' => $product->status,
                    ]);
                }
            } while (!empty($buffer));
        });
    }

    /**
     * Return ALL the products in the persistence system.
     *
     * @return Collection<int, Product>|LazyCollection<int, Product>
     */
    public function all(): Collection|LazyCollection
    {
        return $this->match();
    }

    public function save(Product|Collection|LazyCollection $product): Product|Collection|LazyCollection
    {
        return $product instanceof Product
            ? $this->saveOne($product)
            : $this->saveMany($product);
    }

    private function saveOne(Product $product): Product
    {
        if ($product->id === null) { // CREATE
            $response = $this->post('products', [
                'name' => $product->name,
                'sku' => $product->sku,
                'status' => $product->status,
            ]);
            $product->id = $response->id; // Append the newly provided id.
        } else { // UPDATE
            $this->put("products/$product->id", [
                'name' => $product->name,
                'sku' => $product->sku,
                'status' => $product->status,
            ]);
        }
        return $product;
    }

    /**
     * Persists many products at once.
     *
     * @param  Collection<int, Product>|LazyCollection<int, Product>  $products
     * @return Collection<int, Product>
     */
    private function saveMany(Collection|LazyCollection $products): Collection
    {
        /** Collection<int, Product> $result */
        $result = new Collection();

        $products
            ->chunk(50)
            /** Collection<int, Product>|LazyCollection<int, Product> $products */
            ->each(function (Collection|LazyCollection $products) use ($result) {
                $payload = $products->reduce(function (array $payload, Product $product) {
                    if ($product->id === null) { // CREATE
                        $payload['create'][] = [
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'status' => $product->status,
                        ];
                    } else { // UPDATE
                        $payload['update'][] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'status' => $product->status,
                        ];
                    }
                    return $payload;
                }, ['create' => [], 'update' => []]);

                $response = $this->post('products/batch', $payload);

                if (isset($response->create)) {
                    $result->push(...$response->create);
                }

                if (isset($response->update)) {
                    $result->push(...$response->update);
                }
            });

        return $result
            ->map(function (object $product) {
                return new Product([
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'status' => $product->status,
                ]);
            })
            ->values();
    }
}
