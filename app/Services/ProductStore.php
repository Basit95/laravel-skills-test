<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Str;
use RuntimeException;

class ProductStore
{
    public function all(): array
    {
        return $this->withLock(LOCK_SH, function () {
            return $this->read();
        });
    }

    public function save(array $values, ?string $id = null): array
    {
        return $this->withLock(LOCK_EX, function () use ($values, $id) {
            $products = $this->read();

            if ($id === null) {
                abort_if(
                    count($products) >= 1000,
                    422,
                    'The inventory supports up to 1,000 products.'
                );

                $products[] = [
                    'id' => (string) Str::uuid(),
                    ...$values,
                    'submitted_at' => now('UTC')
                        ->format('Y-m-d\TH:i:s.u\Z'),
                ];
            } else {
                $index = array_search(
                    $id,
                    array_column($products, 'id'),
                    true
                );

                abort_if(
                    $index === false,
                    404,
                    'This product could not be found.'
                );

                $products[$index] = array_merge(
                    $products[$index],
                    $values
                );
            }

            $this->write($products);

            return $products;
        });
    }

    private function withLock(int $mode, Closure $callback): array
    {
        $handle = fopen(storage_path('app/products.lock'), 'c');

        if ($handle === false) {
            throw new RuntimeException('Could not open inventory storage.');
        }

        try {
            if (!flock($handle, $mode)) {
                throw new RuntimeException('Could not lock inventory storage.');
            }

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function read(): array
    {
        $path = storage_path('app/products.json');

        if (!is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Could not read inventory data.');
        }

        $products = json_decode(
            $contents,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($products) || !array_is_list($products)) {
            throw new RuntimeException('Inventory data must be a JSON array.');
        }

        return $products;
    }

    private function write(array $products): void
    {
        $json = json_encode(
            $products,
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR
        ) . PHP_EOL;

        $temporary = tempnam(storage_path('app'), 'inventory-');

        if ($temporary === false) {
            throw new RuntimeException('Could not create an inventory file.');
        }

        try {
            $written = file_put_contents($temporary, $json);

            if ($written !== strlen($json)) {
                throw new RuntimeException('Could not write inventory data.');
            }

            // Replace the complete file while holding the separate lock.
            if (!rename($temporary, storage_path('app/products.json'))) {
                throw new RuntimeException('Could not save inventory data.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}